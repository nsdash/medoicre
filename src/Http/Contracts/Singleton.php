<?php

namespace Mediocre\Http\Contracts;

interface Singleton
{
  public static function init(): self;
}
