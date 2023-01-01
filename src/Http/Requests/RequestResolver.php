<?php

declare(strict_types=1);

namespace Mediocre\Http\Requests;

use Mediocre\Http\Contracts\MiddlewareInterface;
use Mediocre\Http\Contracts\ResponseInterface;
use Mediocre\Http\Responses\JsonResponse;
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

    if ($request->getHttpMethod() === 'OPTIONS') {
      return $this->optionsResponse($request, $router);
    }

    return $this->routerResponse($request, $router);
  }

  private function optionsResponse(Request $request, RouterInterface $router): ResponseInterface
  {
    $methods = implode(',', $router->getItemMethods($request->getUri()) ?? []);

    return new JsonResponse(null, 200, array_merge([
        "Access-Control-Allow-Methods:$methods",
      ], config('cors')
      )
    );
  }

  private function routerResponse(Request $request, RouterInterface $router): ResponseInterface
  {
    $route = $router->getItem($request->getUri(), $request->getHttpMethod());

    $this->handleMiddleware($route, $request);

    $data = $this->dependencyResolver
      ->resolveMethod($route->getClass(), $route->getMethod());

    if ($data instanceof ResponseInterface) {

      return $data;
    }

    return new Response($data);
  }


  /** @throws ReflectionException */
  private function handleMiddleware(RouteItem $route, Request $request): void
  {
    $middlewareClass = $route->getMiddlewareClass();

    if (!$middlewareClass) {
      return;
    }

    /** @var MiddlewareInterface $middleware */
    $middleware = $this->dependencyResolver->resolveClass($middlewareClass);

    $middleware->handle($request);

  }
}
