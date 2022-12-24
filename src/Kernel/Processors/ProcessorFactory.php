<?php

declare(strict_types=1);

namespace Mediocre\Kernel\Processors;

use Mediocre\Kernel\Contracts\AppProcessorInterface;
use Mediocre\Kernel\Detectors\ContextDetector;
use Mediocre\Kernel\Processors\Cli\CliProcessor;
use Mediocre\Kernel\Processors\Web\WebProcessor;
use Mediocre\Shared\Reflection\DependencyInjection\DependencyResolver;
use ReflectionException;

final class ProcessorFactory
{
  /** @throws ReflectionException */
  public static function makeStrategy(): AppProcessorInterface
  {
    $resolver = DependencyResolver::init();

    return match (ContextDetector::getContext()) {
      'cli' => $resolver->resolveClass(CliProcessor::class),
      default => $resolver->resolveClass(WebProcessor::class)
    };
  }
}
