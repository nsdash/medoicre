<?php

declare(strict_types=1);

namespace Mediocre\Kernel\Contracts;

interface AppProcessorInterface
{
  public function process(): void;
}
