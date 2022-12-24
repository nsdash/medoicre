<?php

declare(strict_types=1);

use Mediocre\Kernel\Detectors\ContextDetector;

function commands(): array
  {
    return include __DIR__ . '/../../../../../../config/commands.php';
  }

  function config(string $key, mixed $default = null): mixed
  {
    $map = include __DIR__ . '/../../../../../../config/config.php';

    return $map[$key] ?? $default;
  }

  function providers(): array
  {
    return include __DIR__ . '/../../../../../../config/providers.php';
  }

/** @throws JsonException */
  function fatalHandler() : void
  {
    $error = error_get_last();

    if(!is_null($error)) {
      $errorNumber = $error["type"];
      $errorFile = $error["file"];
      $errorLine = $error["line"];
      $errorStr  = $error["message"];

      echo json(traceError($errorNumber, $errorStr, $errorFile, $errorLine));
    }
  }

  /** @throws JsonException */
  function json(mixed $content): string
  {
    return json_encode($content, JSON_THROW_ON_ERROR);
  }

  function traceError(
    string|int $errorNumber,
    string $errorStr,
    ?string $errorFile,
    string|int|null $errorLine,
    array $errorDetails = []
  ): array
  {
    if (ContextDetector::isCLI()) {
      return [$errorStr];
    }

    $content = [
      'errorCode' => $errorNumber,
      'errorDetails' => $errorDetails
    ];

    if (!config('debug', false)) {
      return $content;
    }

    $trace = print_r(debug_backtrace(0), true);

    $content['errorMessage'] = $errorStr;


    if ($errorFile) {
      $content['errorFile'] = $errorFile;
    }

    if ($errorLine) {
      $content['errorLine'] = $errorLine;
    }

    $content['trace'] = debug_backtrace(0);

    return $content;
  }

  register_shutdown_function( "fatalHandler" );

