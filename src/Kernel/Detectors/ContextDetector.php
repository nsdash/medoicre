<?php

declare(strict_types=1);

namespace Mediocre\Kernel\Detectors;

final class ContextDetector
{
  public static function getContext(): ?string
  {
    return php_sapi_name() ?? null;
  }

  public static function isCLI(): bool
  {
    return php_sapi_name() === 'cli';
  }
}
