<?php

namespace Mediocre\Shared\Exceptions;

use Exception;
use Throwable;

final class BadRequestHttpException extends Exception
{
  private array $details = [];

  public function getDetails(): array
  {
    return $this->details;
  }

  public function setDetails(array $details): void
  {
    $this->details = $details;
  }

  public static function withDetails(
    array $details,
    string $message = '',
    int $code = 1,
    ?Throwable $previous = null
  ): BadRequestHttpException
  {
    $instance = new self($message, $code, $previous);

    $instance->setDetails($details);

    return $instance;
  }
}
