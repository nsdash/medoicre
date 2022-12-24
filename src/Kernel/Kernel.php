<?php

declare(strict_types=1);

namespace Mediocre\Kernel;

use Mediocre\Kernel\Processors\ProcessorFactory;
use ReflectionException;

final class Kernel
{
  /** @throws ReflectionException */
  public static function run(): void
  {
    ProcessorFactory::makeStrategy()->process();
  }
}
