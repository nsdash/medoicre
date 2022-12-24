<?php

declare(strict_types=1);

namespace Mediocre\Migrations\Commands;

use Mediocre\Cli\Input\Input;
use Mediocre\Shared\Reflection\DependencyInjection\DependencyResolver;
use ReflectionException;
use RuntimeException;
use Mediocre\Cli\Contracts\CliCommandInterface;

use Mediocre\Cli\Output\Output;
use Mediocre\Db\Drivers\Mysql\Builder\QueryBuilder;
use Mediocre\Migrations\Contracts\MigrationInterface;
use Mediocre\Shared\Reflection\Detectors\ClassDetector;
use Mediocre\Shared\Reflection\Finders\ClassFinder;

final class ExecuteMigrations implements CliCommandInterface
{
  public function __construct(
    private readonly ClassFinder $classFinder,
    private readonly QueryBuilder $queryBuilder,
    private readonly Output $output,
    private readonly Input $input,
    private readonly DependencyResolver $dependencyResolver,
  ) {
  }

  public static function name(): string
  {
    return 'migrate';
  }

  public static function description(): string
  {
    return 'Execute migrations. Available arguments - "rollback:{steps count | number}"';
  }

  /** @throws ReflectionException */
  public function execute(): void
  {
    $this->checkMigrationTable();

    $steps = $this->getRollbackSteps();

    if ($steps) {
      $this->downMigrations($steps);

      return;
    }

    $this->upMigrations();
  }

  private function getRollbackSteps(): false|int
  {
    $arguments = $this->input->getArguments();

    if (!empty($arguments) && str_starts_with($arguments[0], 'rollback')) {
      $rollback = $arguments[0];

      if (!strpos($rollback, ':')) {
        $this->output->redLn('Rollback param must looks like "rollback:{\d}"');

        return false;
      }

      $rollbackSteps = explode(':', $rollback)[1];

      if (!is_numeric($rollbackSteps)) {
        $this->output->redLn('Rollback param must looks like "rollback:{\d}"');

        return false;
      }

      return (int) $rollbackSteps;
    }

    return false;
  }

  private function checkMigrationTable(): void
  {
    $result = $this->queryBuilder->query('SHOW TABLES')->getResult();

    $tables = [];

    foreach ($result as $item) {
      $tables[] = reset($item);
    }

    if (!in_array('migration', $tables, true)) {
      $this->createMigrationTable();
    }
  }

  /** @throws ReflectionException */
  private function upMigrations(): void
  {
    $classes = $this->classFinder->getClassesInNamespace('Migrations');

    $migrated = false;

    foreach ($classes as $class) {
      $alreadyMigrated = $this->queryBuilder->select('migration')->where('name', $class)->isResultNotEmpty();

      if ($alreadyMigrated) {
        continue;
      }

      if (!ClassDetector::isImplements($class, MigrationInterface::class)) {
        throw new RuntimeException('Migration Must Implements MigrationInterface');
      }

      $this->output->greenLn('Migrating: ');
      $this->output->yellowLn($class);

      /** @var MigrationInterface $migration */
      $migration = $this->dependencyResolver->resolveClass($class);

      $migration->up();

      $this->queryBuilder->insert('migration', ['name' => $class])->execute();

      $migrated = true;
    }

    if ($migrated) {
      return;
    }

    $this->output->greenLn('Nothing to Migrate');
  }

  /** @throws ReflectionException */
  private function downMigrations(int $step)
  {
    $alreadyMigrated =
      $this->queryBuilder->select('migration', 'name')
      ->orderByDesc('id')
      ->limit($step)
      ->getResult();

    $rollBacked = false;

    foreach ($alreadyMigrated as $row) {

      $class = $row['name'] ?? '';

      if (!ClassDetector::isImplements($class, MigrationInterface::class)) {
        throw new RuntimeException('Migration Must Implements MigrationInterface');
      }

      $this->output->greenLn('Rolling back: ');
      $this->output->yellowLn($class);

      /** @var MigrationInterface $migration */
      $migration = $this->dependencyResolver->resolveClass($class);

      $migration->down();

      $this->queryBuilder->delete('migration')->where('name', $class)->execute();

      $rollBacked = true;
    }

    if ($rollBacked) {
      return;
    }

    $this->output->greenLn('Nothing to Rollback');

  }

  private function createMigrationTable(): void
  {
    $this->queryBuilder->query(
      'CREATE TABLE migration (
            id int PRIMARY KEY NOT NULL  AUTO_INCREMENT,
            name varchar(255) NOT NULL
        );'
    )->execute();
  }
}
