<?php

declare(strict_types=1);

namespace Mediocre\Http\Contracts;

interface WebHandlerInterface
{
  public function handle(callable $callback): ResponseInterface;
}
