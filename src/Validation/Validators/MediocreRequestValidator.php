<?php

declare(strict_types=1);

namespace Mediocre\Validation\Validators;

use Mediocre\Http\Requests\Request;
use Mediocre\Validation\Exceptions\ValidationException;

abstract class MediocreRequestValidator
{
  private array $errors = [];

  protected ?Request $request = null;

  protected function checkRequiredFields(array $fields): void
  {
    foreach ($fields as $field) {
      if (!$this->request->get($field)) {
        $this->errors[$field][] = "Field $field is mandatory";
      }
    }
  }

  private function hasErrors(): bool
  {
    return !empty($this->errors);
  }

  private function getErrors(): array
  {
    return $this->errors;
  }

  protected function checkArray(mixed $value, string $field, ?string $message = null): void
  {
    if (is_array($this->request->get($field))) {
      return;
    }

    $this->errors[$field][] = $message ?? "Field $field must be array";
  }

  protected function checkString(string $field, ?string $message = null): void
  {
    if (is_string($this->request->get($field))) {
      return;
    }

    $this->errors[$field][] = $message ?? "Field $field must be string";
  }

  protected function checkNumeric(string $field, ?string $message = null): void
  {
    if (is_numeric($this->request->get($field))) {
      return;
    }

    $this->errors[$field][] = $message ?? "Field $field must be numeric";
  }

  protected function checkBool(string $field, ?string $message = null): void
  {
    if (is_bool($this->request->get($field))) {
      return;
    }

    $this->errors[$field][] = $message ?? "Field $field must be bool";
  }

  protected function checkLength(string $field, int $count, ?string $message = null): void
  {
    if (is_bool($this->request->get($field))) {
      return;
    }

    $this->errors[$field][] = $message ?? "Field $field must be bool";
  }

  public static function validate(Request $request, ...$args): void
  {
    $instance = new static($request, ...$args);

    if (!$instance->request) {
      throw new \LogicException('Request not provided to ' . $instance::class);
    }

    $instance->checkRules();

    if ($instance->hasErrors()) {
      throw (new ValidationException())->setErrors($instance->getErrors());
    }
  }

  abstract protected function checkRules(): void;
}
