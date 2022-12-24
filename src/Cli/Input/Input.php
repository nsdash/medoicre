<?php

declare(strict_types=1);

namespace Mediocre\Cli\Input;

class Input
{
  public function enter(string $message): ?string
  {
    return readline($message) ?? null;
  }

  public function getAllArguments(): array
  {
    return $_SERVER['argv'] ?? [];
  }

  public function getArguments(): array
  {
    $arguments = $this->getAllArguments();

    unset($arguments[0]);
    unset($arguments[1]);

    return array_values($arguments);
  }

  public function getFirstArgument(): ?string
  {
    return $_SERVER['argv'][1] ?? null;
  }
}
