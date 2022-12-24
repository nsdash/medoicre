<?php

declare(strict_types=1);

namespace Mediocre\Cli\Output;

final class Output
{
  public function info(string $message): void
  {
    echo $message;
  }

  public function infoLn(string $message): void
  {
    echo $message . "\n";
  }

  public function green(string $message): void
  {
    echo "\e[32m" . $message . "\033[0m";
  }

  public function greenLn(string $message): void
  {
    echo "\e[32m" . $message . "\033[0m\n";
  }

  public function yellow(string $message): void
  {
    echo "\e[33m" . $message . "\033[0m";
  }

  public function yellowLn(string $message): void
  {
    echo "\e[33m" . $message . "\033[0m\n";
  }

  public function red(string $message): void
  {
    echo "\e[31m" . $message . "\033[0m";
  }

  public function redLn(string $message): void
  {
    echo "\e[31m" . $message . "\033[0m\n";
  }

}
