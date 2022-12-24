<?php

declare(strict_types=1);

namespace Mediocre\Db\Drivers\Mysql;

use Mediocre\Db\Exceptions\ConnectionException;
use Mediocre\Http\Contracts\Singleton;
use PDO;

final class Connection extends PDO implements Singleton
{
  private static ?self $instance = null;

  public static function init(): self
  {
    if (!self::$instance) {
      self::$instance = self::setConnection();
    }

    return self::$instance;
  }

  /** @throws ConnectionException */
  private static function setConnection(): PDO
  {
    $host = config('databaseHost');
    $name = config('databaseName');
    $user = config('databaseUser');
    $password = config('databasePassword');

    if (!$host) {
      throw new ConnectionException('DB Host is invalid');
    }

    if (!$name) {
      throw new ConnectionException('DB Name is invalid');
    }

    if (!$user) {
      throw new ConnectionException('DB User is invalid');
    }

    return new self("mysql:host=$host;dbname=$name", $user, $password);
  }
}
