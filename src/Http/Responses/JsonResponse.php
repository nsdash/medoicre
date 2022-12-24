<?php

declare(strict_types=1);

namespace Mediocre\Http\Responses;

final class JsonResponse extends Response
{
  protected array $headers = [];

  public function __construct(
    protected readonly mixed $content,
    protected readonly int $status = 200,
    array $headers = [],
  ) {
    $this->headers[] = 'Content-Type: application/json';
  }
}
