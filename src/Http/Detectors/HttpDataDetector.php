<?php

declare(strict_types=1);

namespace Mediocre\Http\Detectors;

final class HttpDataDetector
{
  public static function getMethod(): string
  {
    return $_SERVER['REQUEST_METHOD'];
  }

  public static function getCurrentUri()
  {
    return $_SERVER['REQUEST_URI'];
  }
}
