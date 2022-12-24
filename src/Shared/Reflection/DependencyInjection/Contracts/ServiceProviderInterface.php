<?php

namespace Mediocre\Shared\Reflection\DependencyInjection\Contracts;

use Mediocre\Shared\Reflection\DependencyInjection\Container;

interface ServiceProviderInterface
{
  public function register(Container $container): void;
}
