<?php

declare(strict_types=1);

namespace Mediocre\Http\Requests;

use Mediocre\Http\Contracts\MiddlewareInterface;
use Mediocre\Http\Contracts\ResponseInterface;
use Mediocre\Http\Responses\Response;
use Mediocre\Http\Router\Contracts\RouterInterface;
use Mediocre\Http\Router\Dto\RouteItem;
use Mediocre\Shared\Exceptions\WrongHttpMethodException;
use Mediocre\Shared\Reflection\DependencyInjection\DependencyResolver;
use ReflectionException;
use Throwable;

final class RequestResolver
{
  private readonly DependencyResolver $dependencyResolver;

  public function __construct()
  {
    $this->dependencyResolver = DependencyResolver::init();
  }

  /** @throws Throwable */
  public function sendRequest(RouterInterface $router): ResponseInterface
  {
    $request = Request::init();

    $route = $router->getItem($request->getUri());

    $this->checkMethod($route, $request);

    $this->handleMiddleware($route, $request);

    $data = $this->dependencyResolver
      ->resolveMethod($route->getClass(), $route->getMethod());

    if ($data instanceof ResponseInterface) {
      return $data;
    }

    return new Response($data);
  }

  /** @throws WrongHttpMethodException */
  private function checkMethod(RouteItem $route, Request $request): void
  {
    if ($route->getHttpMethod() !== $request->getHttpMethod()) {
      throw new WrongHttpMethodException('Wrong HTTP Method');
    }
  }

  /** @throws ReflectionException */
  private function handleMiddleware(RouteItem $route, Request $request): void
  {
    $middlewareClass = $route->getMiddlewareClass();

    if(!$middlewareClass) {
      return;
    }

    /** @var MiddlewareInterface $middleware */
    $middleware = $this->dependencyResolver->resolveClass($middlewareClass);

    $middleware->handle($request);

  }
}
