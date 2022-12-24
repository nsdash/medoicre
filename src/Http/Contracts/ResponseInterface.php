<?php

namespace Mediocre\Http\Contracts;

use Throwable;

interface ResponseInterface
{
  /** @throws Throwable */
  public function getContent(): ?string;

  public function getStatus(): int;

  public function getHeaders(): array;
}
