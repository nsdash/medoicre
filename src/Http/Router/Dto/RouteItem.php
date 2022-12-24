<?php

declare(strict_types=1);

namespace Mediocre\Http\Router\Dto;

use Mediocre\Http\Contracts\MiddlewareInterface;
use Mediocre\Shared\Reflection\Detectors\ClassDetector;
use RuntimeException;

final class RouteItem
{
  private ?string $middlewareClass = null;

  public function __construct(
    private readonly string $httpMethod,
    private readonly string $url,
    private readonly string $class,
    private readonly string $method,
  ) {
  }

  /** @return string|null */
  public function getMiddlewareClass(): ?string
  {
    return $this->middlewareClass;
  }

  /**
   * @param string|null $middlewareClass
   */
  public function setMiddlewareClass(?string $middlewareClass): void
  {
    if (!ClassDetector::isImplements($middlewareClass, MiddlewareInterface::class)) {
      throw new RuntimeException(
        "Provided middleware class ($middlewareClass) should implements Middleware Interface"
      );
    }

    $this->middlewareClass = $middlewareClass;
  }


  /**
   * @return string
   */
  public function getUrl(): string
  {
    return $this->url;
  }

  /**
   * @return string
   */
  public function getClass(): string
  {
    return $this->class;
  }

  /**
   * @return string
   */
  public function getMethod(): string
  {
    return $this->method;
  }

  /**
   * @return string
   */
  public function getHttpMethod(): string
  {
    return $this->httpMethod;
  }
}
