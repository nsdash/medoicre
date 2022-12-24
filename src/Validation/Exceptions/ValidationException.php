<?php

declare(strict_types=1);

namespace Mediocre\Validation\Exceptions;

use Exception;

final class ValidationException extends Exception
{
  private $errors = [];

  public function getErrors(): array
  {
    return $this->errors;
  }

  public function setErrors(array $errors): self
  {
    $this->errors = $errors;

    return $this;
  }
}
