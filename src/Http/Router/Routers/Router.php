<?php

declare(strict_types=1);

namespace Mediocre\Http\Router\Routers;

use Mediocre\Http\Detectors\HttpDataDetector;
use Mediocre\Http\Router\Contracts\RouterInterface;
use Mediocre\Http\Router\Dto\RouteItem;
use Mediocre\Shared\Exceptions\ItemNotFoundException;
use RuntimeException;

final class Router implements RouterInterface
{
  /** @var RouteItem[]  */
  private array $routes = [];

  /** @throws ItemNotFoundException */
  public function getItem(string $url): RouteItem
  {
    $url = $this->transformUrl($url);

    return $this->routes[HttpDataDetector::getMethod()][$url]
      ?? throw new ItemNotFoundException('Route not found');
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

    if (isset($this->routes[$httpMethod][$url])) {
        throw new RuntimeException("Route $url with HTTP method $httpMethod already registered");
    }

    $this->routes[$httpMethod][$url] = $item;

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
