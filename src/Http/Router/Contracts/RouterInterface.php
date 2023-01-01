<?php

namespace Mediocre\Http\Router\Contracts;

use Mediocre\Http\Router\Dto\RouteItem;

interface RouterInterface
{
  public function getItem(string $url, string $methodName): RouteItem;

  public function getItemMethods(string $url): ?array;

  public function get(string $url, string $className, string $methodName): RouteItem;

  public function post(string $url, string $className, string $methodName): RouteItem;

  public function delete(string $url, string $className, string $methodName): RouteItem;

  public function put(string $url, string $className, string $methodName): RouteItem;
}
