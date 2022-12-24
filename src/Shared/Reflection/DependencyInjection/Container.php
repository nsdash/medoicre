<?php

declare(strict_types=1);

namespace Mediocre\Shared\Reflection\DependencyInjection;

final class Container
{
  private array $services = [];

  public static function init(): self
  {
    return new self();
  }

  public function add(string $key, mixed $concrete): void
  {
    $this->services[$key] = $concrete;
  }

  public function getServices(): array
  {
    return $this->services;
  }
}
