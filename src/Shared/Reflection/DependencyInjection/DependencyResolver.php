<?php

declare(strict_types=1);

namespace Mediocre\Shared\Reflection\DependencyInjection;

use Mediocre\Http\Contracts\Singleton;
use Mediocre\Shared\Reflection\DependencyInjection\Contracts\ServiceProviderInterface;
use Mediocre\Shared\Reflection\Detectors\ClassDetector;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

final class DependencyResolver implements Singleton
{
  private function __construct()
  {}

  private static ?self $instance = null;

  private ?Container $container = null;

  public static function init(): self
  {
    if (!self::$instance) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  /** @throws ReflectionException */
  public function resolveMethod(string $class, string $method): mixed
  {
    $method = $this->getReflection($class)->getMethod($method);

    $params = $method->getParameters();

    return $method->invokeArgs($this->resolveClass($class), $this->getNewInstanceParams($params));
  }

  /** @throws ReflectionException */
  public function resolveClass(string $class, int $attempt = 1): mixed
  {
    $isSingleton = ClassDetector::isImplements($class, Singleton::class);

    if($isSingleton) {
      /** @var $class Singleton */
      return $class::init();
    }

    $isServiceProvider = ClassDetector::isImplements($class, ServiceProviderInterface::class);

    if ($attempt < 2 && !$isServiceProvider) {
      $resolved = $this->resolveFromServiceContainer($class, $attempt);

      if (!is_null($resolved)) {
        return $resolved;
      }
    }

    $reflectionClass = $this->getReflection($class);

    $constructor = $reflectionClass->getConstructor();

    if (is_null($constructor)) {
      return $reflectionClass->newInstance();
    }

    $params = $constructor->getParameters();

    if (empty($params)) {
      return $reflectionClass->newInstance();
    }

    $newInstanceParams = $this->getNewInstanceParams($params);

    return $reflectionClass->newInstanceArgs(
      $newInstanceParams
    );
  }

  /** @throws ReflectionException */
  private function resolveFromServiceContainer(string $class, int $attempt): ?object
  {
    if (!$this->container) {
      $this->container = $this->initContainer($attempt);
    }

    $services = $this->container->getServices();

    if (isset($services[$class])) {
      $service = $services[$class];

      if (is_callable($service)) {
        $resolved = $service();

        if (!is_object($resolved)) {
          throw new RuntimeException('Service must be an object');
        }

        return $resolved;
      }

      if(is_string($service)) {
        $resolved = $this->resolveClass($service, $attempt + 1);

        if (!is_object($resolved)) {
          throw new RuntimeException('Service must be an object');
        }

        return $resolved;
      }

      if(is_object($service)) {
        return $service;
      }

      throw new RuntimeException(
        'Wrong service type provided in service container (allowed types: object, callable, string)'
      );
    }

    return null;
  }

  /** @throws ReflectionException */
  private function initContainer(int $attempt): Container
  {
    $providers = providers();

    $container = Container::init();

    foreach ($providers as $providerClass) {
      $provider = $this->resolveClass($providerClass, $attempt);

      if (!$provider instanceof ServiceProviderInterface) {
        throw new RuntimeException('Provider in providers.php must be instance if ServiceProviderInterface');
      }

      $provider->register($container);
    }

    return $container;
  }

  /** @throws ReflectionException */
  private function getReflection(string $class): ReflectionClass
  {
    return new ReflectionClass($class);
  }

  /** @throws ReflectionException */
  private function getNewInstanceParams(array $params): array
  {
    $newInstanceParams = [];

    foreach ($params as $param) {
      if (is_null($param->getType())) {
        $newInstanceParams[] =  $param->getDefaultValue();

        continue;
      }

      $typeName = $param->getType()->getName();

      $newInstanceParams[] = $this->resolveClass($typeName);
    }

    return $newInstanceParams;
  }
}
