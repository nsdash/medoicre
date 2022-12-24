<?php

declare(strict_types=1);

namespace Mediocre\Validation;

use DomainException;
use Mediocre\Validation\Exceptions\AssertException;

final class Assert
{
  /** @throws AssertException */
  public static function length(string $value, int $count, ?string $message = null): void
  {
    if (strlen($value) > $count) {
      throw new AssertException($message ?? "Value can not be longer than $count symbols");
    }
  }

  /** @throws AssertException */
  public static function positive(float|int $value, ?string $message = null): void
  {
    if ($value < 0) {
      throw new AssertException($message ?? 'Value can not be negative');
    }
  }

  /** @throws AssertException */
  public static function gt(float|int $value, float|int $compareWith, ?string $message = null)
  {
    if ($value <= $compareWith) {
      throw new AssertException($message ?? "Value should be greater than $compareWith");
    }
  }

  /** @throws AssertException */
  public static function decimalsCount(float $decimal, int $count, ?string $message = null): void
  {
    $exploded = strrchr((string) $decimal, ".");

    if (!$exploded) {
      return;
    }

    if (strlen(substr($exploded, 1) > $count)) {
      throw new AssertException($message ?? "Decimals count should be $count");
    }
  }
}
