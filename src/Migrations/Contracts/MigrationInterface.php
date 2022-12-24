<?php

declare(strict_types=1);

namespace Mediocre\Migrations\Contracts;

interface MigrationInterface
{
  public function up(): void;

  public function down(): void;
}
