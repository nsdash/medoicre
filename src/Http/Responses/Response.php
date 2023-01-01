<?php

declare(strict_types=1);

namespace Mediocre\Http\Responses;

use JsonException;
use Mediocre\Http\Contracts\ResponseInterface;

class Response implements ResponseInterface
{
  public function __construct(
    protected readonly mixed $content,
    protected readonly int $status = 200,
    protected array $headers = []
  ) {
  }

  /**
   * @return string|null
   * @throws JsonException
   */
  public function getContent(): ?string
  {
    if (is_null($this->content)) {
      return null;
    }

    if (is_string($this->content)) {
      return $this->content;
    }

    return json($this->content);
  }

  /**
   * @return int
   */
  public function getStatus(): int
  {
    return $this->status;
  }

  /**
   * @return array
   */
  public function getHeaders(): array
  {
    return $this->headers;
  }
}
