<?php

declare(strict_types=1);

namespace Mediocre\Http\Contracts;

use Mediocre\Http\Requests\Request;

interface MiddlewareInterface
{
  public function handle(Request $request): void;
}
