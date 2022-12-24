<?php

declare(strict_types=1);

namespace Mediocre\Cli\Contracts;

interface CliCommandInterface
{
  public static function name(): string;

  public static function description(): string;

  public function execute(): void;
}
