<?php

declare(strict_types=1);

namespace Mediocre\Http;

use Mediocre\Http\Contracts\WebHandlerInterface;
use Mediocre\Http\Handlers\WebHandler;
use Mediocre\Http\Requests\RequestResolver;
use Mediocre\Kernel\Processors\Web\WebProcessor;
use Mediocre\Shared\Reflection\DependencyInjection\Container;
use Mediocre\Shared\Reflection\DependencyInjection\Contracts\ServiceProviderInterface;
use Mediocre\Shared\Reflection\DependencyInjection\DependencyResolver;

final class ModuleProvider implements ServiceProviderInterface
{
  public function register(Container $container): void
  {
    $container->add(WebHandlerInterface::class, WebHandler::class);

    $container->add(WebProcessor::class, function () {
      $dependencyResolver = DependencyResolver::init();

      return new WebProcessor(
        $dependencyResolver->resolveClass(WebHandlerInterface::class),
        $dependencyResolver->resolveClass(RequestResolver::class),
        config('routesFile'),
      );
    });
  }
}
