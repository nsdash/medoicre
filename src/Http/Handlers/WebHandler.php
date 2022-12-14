<?php

namespace Mediocre\Http\Handlers;

use Mediocre\Http\Contracts\ResponseInterface;
use Mediocre\Http\Contracts\WebHandlerInterface;
use Mediocre\Http\Responses\JsonResponse;
use Mediocre\Http\Responses\Response;
use Mediocre\Shared\Exceptions\BadRequestHttpException;
use Mediocre\Shared\Exceptions\NotAllowedException;
use Mediocre\Shared\Exceptions\WrongHttpMethodException;
use Mediocre\Shared\Exceptions\ItemNotFoundException;
use Mediocre\Validation\Exceptions\AssertException;
use Mediocre\Shared\Exceptions\UnauthorizedException;
use Throwable;
use DomainException;

class WebHandler implements WebHandlerInterface
{
  public function handle(callable $callback): ResponseInterface
  {
    try {
      return $callback();
    } catch (ItemNotFoundException $exception) {
      return new JsonResponse(
        traceError($exception->getCode(), 'Page not found', null, null),
        404,
      );
    } catch (WrongHttpMethodException $exception) {
      return new JsonResponse(
        traceError($exception->getCode(), 'Wrong Http Method', null, null),
        405,
      );
    } catch (DomainException $exception) {
      return new JsonResponse(
        traceError($exception->getCode(), $exception->getMessage(), null, null),
        422,
      );
    } catch (BadRequestHttpException|AssertException $exception) {
      return new JsonResponse(
        traceError(
          $exception->getCode(),
            $exception->getMessage() ?? 'Bad Request',
          null,
          null,
          $exception->getDetails()
        ),
        400,
      );
    } catch (NotAllowedException $exception) {
      return new JsonResponse(
        traceError($exception->getCode(), 'Not Allowed', null, null),
        403,
      );
    } catch (UnauthorizedException $exception) {
      return new JsonResponse(
        traceError($exception->getCode(), 'Unauthorized', null, null),
        401,
      );
    } catch (Throwable $exception) {
      return new JsonResponse(
        traceError(
          $exception->getCode(),
          "Unknown Error: {$exception->getMessage()}",
          $exception->getFile(),
          $exception->getLine()
        ),
        500,
      );
    }
  }
}
