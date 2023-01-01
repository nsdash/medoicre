<?php

declare(strict_types=1);

namespace Mediocre\Http\Requests;

use Mediocre\Http\Contracts\Singleton;
use Mediocre\Http\Detectors\HttpDataDetector;
use Mediocre\Shared\Exceptions\BadRequestHttpException;

final class Request implements Singleton
{
  private static ?self $instance = null;

  private function __construct(
    private readonly string $httpMethod,
    private readonly string $url,
  ) {
  }

  public function all(): array
  {
    $jsonBody = $this->getJsonData();

    return array_merge($_GET, $_POST, $jsonBody);
  }

  public function get(string $key): mixed
  {
    $data = $this->all();

    return $data[$key] ?? null;
  }

  public function file(string $key): mixed
  {
    return $_FILES[$key] ?? null;
  }

  public function files(): array
  {
    return $_FILES;
  }

  public static function init(): self
  {
    if (!self::$instance) {
      self::$instance = new self(
        HttpDataDetector::getMethod(),
        HttpDataDetector::getCurrentUri()
      );
    }

    return self::$instance;
  }

  public function getHttpMethod(): string
  {
    return $this->httpMethod;
  }

  public function getUrl(): string
  {
    return $this->url;
  }

  public function getUri(): string
  {
    return strtok($this->url, '?');
  }

  public function getHeaders()
  {
    $headers = [];

    foreach($_SERVER as $key => $value) {

      if (substr($key, 0, 5) <> 'HTTP_') {
        continue;
      }

      $header = $this->transformHeaderName($key);

      $headers[$header] = $value;

    }
    return $headers;
  }

  public function getHeader(string $key): mixed
  {
    $headers = $this->getHeaders();

    return $headers[$key] ?? null;
  }

  public function getBearerToken(): string
  {
    $token = $this->getHeader('Authorization') ?? '';

    $token = str_replace('Bearer', '', $token);

    return preg_replace('/\s+/', '', $token) ?? '';
  }

  private function transformHeaderName(string $header): string
  {
    $header = str_replace('_', ' ', strtolower(substr($header, 5)));

    return str_replace(' ', '-', ucwords($header));
  }

  public function isJson(): bool
  {
    return $this->getHeader('Content-Type') === 'application/json';
  }

  private function getJsonData(): array
  {
    if (!$this->isJson()) {
      return [];
    }

    $input = file_get_contents('php://input');

    try {
      return json_decode($input, true, 512, JSON_THROW_ON_ERROR);
    } catch (\JsonException $e) {
      throw new BadRequestHttpException('Invalid Json provided');
    }
  }
}
