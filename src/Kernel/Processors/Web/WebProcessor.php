<?php

declare(strict_types=1);

namespace Mediocre\Kernel\Processors\Web;

use Mediocre\Http\Contracts\ResponseInterface;
use Mediocre\Http\Contracts\WebHandlerInterface;
use Mediocre\Http\Requests\RequestResolver;
use Mediocre\Http\Router\Routers\Router;
use Mediocre\Kernel\Contracts\AppProcessorInterface;
use Throwable;

final class WebProcessor implements AppProcessorInterface
{
  public function __construct(
    private readonly WebHandlerInterface $webHandler,
    private readonly RequestResolver $requestResolver,
    private readonly string $routesPath
  ) {
  }

  /** @throws Throwable */
  public function process(): void
  {
    $response = $this->webHandler->handle(fn() => ($this->sendRequest()));

    foreach ($response->getHeaders() as $header) {
      header($header);
    }

    http_response_code($response->getStatus());

    if (is_null($response->getContent())) {
      return;
    }

    print $response->getContent();
  }

  /** @throws Throwable */
  private function sendRequest(): ResponseInterface
  {
    $router = $this->getRegisteredRoutes();

    return $this->requestResolver->sendRequest($router);
  }

  private function getRegisteredRoutes(): Router
  {
    $callback = function(): Router {
      $router = new Router();

      return include $this->routesPath;
    };

    return $callback();
  }
}
