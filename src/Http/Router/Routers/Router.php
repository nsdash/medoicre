<?php

declare(strict_types=1);

namespace Mediocre\Http\Router\Routers;

use Mediocre\Http\Detectors\HttpDataDetector;
use Mediocre\Http\Router\Contracts\RouterInterface;
use Mediocre\Http\Router\Dto\RouteItem;
use Mediocre\Shared\Exceptions\ItemNotFoundException;
use Mediocre\Shared\Exceptions\WrongHttpMethodException;
use RuntimeException;


final class Router implements RouterInterface
{
  /** @var RouteItem[]  */
  private array $routes = [];

  /** @throws ItemNotFoundException */
  public function getItem(string $url, string $methodName): RouteItem
  {
    $url = $this->transformUrl($url);

    $methods = $this->routes[$url] ?? null;

    if (!$methods) {
      throw new ItemNotFoundException('Route not found');
    }

    if (!isset($methods[$methodName])) {
      throw new WrongHttpMethodException('Http Method Not Allowed');
    }

    return $methods[$methodName]
      ?? throw new ItemNotFoundException('Route not found');
  }

  public function getItemMethods(string $url): ?array
  {
    $url = $this->transformUrl($url);

    $result =  $this->routes[$url] ?? null;

    if (empty($result)) {
      return null;
    }

    return array_keys($result);
  }

  public function get(string $url, string $className, string $methodName): RouteItem
  {
    return $this->add('GET', $url, $className, $methodName);
  }

  public function post(string $url, string $className, string $methodName): RouteItem
  {
    return $this->add('POST', $url, $className, $methodName);
  }

  public function delete(string $url, string $className, string $methodName): RouteItem
  {
    return $this->add('DELETE', $url, $className, $methodName);
  }

  public function put(string $url, string $className, string $methodName): RouteItem
  {
    return $this->add('PUT', $url, $className, $methodName);
  }

  private function add(string $httpMethod, string $url, string $className, string $methodName): RouteItem
  {
    $url = $this->transformUrl($url);

    $item = new RouteItem($httpMethod, $url, $className, $methodName);

    if (isset($this->routes[$url][$httpMethod])) {
        throw new RuntimeException("Route $url with HTTP method $httpMethod already registered");
    }

    $this->routes[$url][$httpMethod] = $item;

    return $item;
  }

  private function transformUrl(string $url): string
  {
    if ($url[0] === '/') {
      $url = substr($url, 1);
    }

    return match ($url) {
      '\\', '' => '/',
      default => $url,
    };
  }
}
