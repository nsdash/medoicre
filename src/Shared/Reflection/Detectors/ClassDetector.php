<?php

declare(strict_types=1);

namespace Mediocre\Shared\Reflection\Detectors;

final class ClassDetector
{
  public static function isImplements(string $class, string $interface): bool
  {
    $implements = class_implements($class);

    return in_array($interface, $implements, true);
  }
}
