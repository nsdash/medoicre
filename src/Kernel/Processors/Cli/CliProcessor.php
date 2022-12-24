<?php

namespace Mediocre\Kernel\Processors\Cli;

use Mediocre\Migrations\Commands\ExecuteMigrations;
use Mediocre\Cli\Contracts\CliCommandInterface;
use Mediocre\Cli\Input\Input;
use Mediocre\Cli\Output\Output;
use Mediocre\Kernel\Contracts\AppProcessorInterface;
use Mediocre\Shared\Reflection\DependencyInjection\DependencyResolver;
use Mediocre\Shared\Reflection\Detectors\ClassDetector;
use ReflectionException;

final class CliProcessor implements AppProcessorInterface
{
  public function __construct(
    private readonly Input $input,
    private readonly Output $output
  ) {
  }

  /** @throws ReflectionException */
  public function process(): void
  {
    $arguments = $this->input->getAllArguments();

    $commands = $this->getRegisteredCommands();

    if (count($arguments) < 2) {
      $this->printCommands($commands);

      return;
    }

    $commandClassName = $commands[$this->input->getFirstArgument()] ?? null;

    if (!$commandClassName) {
      $this->output->redLn('Command not found!');

      return;
    }

    /** @var CliCommandInterface $command */
    $command = DependencyResolver::init()->resolveClass($commandClassName);

    $command->execute();
  }

  /** @return array<string, class-string<CliCommandInterface>> */
  private function getRegisteredCommands(): array
  {
    $list = commands();

    $commands = [
      ExecuteMigrations::name() => ExecuteMigrations::class,
    ];

    foreach ($list as $item) {
      if (!$this->isCommand($item)) {
        $this->output->redLn('Items in command list must implements CliCommandInterface');
      }

      /** @var $item CliCommandInterface */
      $commands[$item::name()] = $item;
    }

    return $commands;
  }

  private function isCommand(string $class): bool
  {
    return ClassDetector::isImplements($class, CliCommandInterface::class);
  }

  private function printCommands(array $commands): void
  {
      $this->output->greenLn('|  Commands List ');

      $counter = 1;
      foreach ($commands as $name => $command) {
        $this->output->infoLn("| $counter. " . $name . " ({$command::description()})");

        ++$counter;
      }

      $this->output->greenLn('| ___');
  }
}
