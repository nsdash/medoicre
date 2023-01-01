<?php

declare(strict_types=1);

namespace Mediocre\Db\Drivers\Mysql\Builder;

use Mediocre\Db\Drivers\Mysql\Connection;
use PDO;
use PDOStatement;

final class QueryBuilder
{
  private string $query;

  private array $bindings;

  public function __construct(
    private readonly Connection $connection,
  )
  {
  }

  public function query(string $query, array $bindings = []): self
  {
    $this->query = $query;

    $this->bindings = $bindings;

    return $this;
  }

  public function execute(): false|PDOStatement
  {
    $query = $this->connection->prepare($this->query);

    $this->setBindings($query);

    $this->bindings = [];

    $query->execute();

    return $query;
  }

  public function getResult(): array
  {
    return $this->execute()->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getFirstResult(): mixed
  {
    return $this->getResult()[0] ?? null;
  }

  public function isResultNotEmpty(): bool
  {
    return !empty($this->getResult());
  }

  public function delete(string $table): self
  {
    $this->query = "DELETE FROM $table WHERE 1=1";

    return $this;
  }

  public function insert(string $table, array $data): self
  {
    $columns = [];

    foreach ($data as $column => $value) {
      $columns[] = $column;

      $this->addBindings($column, $value);
    }

    $columnsCondition = implode(',', $columns) ;

    $valuesCondition = ':' . implode(',:', $columns);

    $this->query = "INSERT INTO $table ($columnsCondition) VALUES ($valuesCondition)";

    return $this;
  }

  public function select(string $table, string $query = '*'): self
  {
    $this->query = "SELECT $query FROM $table WHERE 1=1";

    return $this;
  }

  public function where(string $field, string|int|float|bool|null $value, string $operator = '='): self
  {
    $this->query .= " AND $field $operator :$field";

    $this->addBindings($field, $value);

    return $this;
  }

  public function orderBy(string $field): self
  {
    $this->query .= " ORDER BY $field ASC";

    return $this;

  }

  public function orderByDesc(string $field): self
  {
    $this->query .= " ORDER BY $field DESC";

    return $this;
  }

  public function limit(int $count): self
  {
    $this->query .= " LIMIT $count";

    return $this;
  }

  public function addBindings(string $key, string|int|bool|float|null $value): void
  {
    $this->bindings[$key] = $value;
  }

  public function getDriver(): Connection
  {
    return $this->connection;
  }

  public function getSql(): string
  {
    return $this->query;
  }

  private function setBindings(PDOStatement $query): void
  {
    foreach ($this->bindings as $key => $binding) {
      $type = null;

      if (is_int($binding)) {
        $type = PDO::PARAM_INT;
      }

      if (is_bool($binding)) {
        $type = PDO::PARAM_BOOL;
      }

      if (is_null($binding)) {
        $type = PDO::PARAM_NULL;
      }

      if (is_null($type)) {
        $type = PDO::PARAM_STR;

        $binding = (string) $binding;
      }

      $query->bindValue($key, $binding, $type);
    }
  }
}
