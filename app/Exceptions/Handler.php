<?php

namespace App\Exceptions;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param \Throwable $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof HttpException) {
            return response()->json(["error" => $exception->getMessage()], $exception->getStatusCode());
        } elseif ($exception instanceof NotFoundResourceException) {
            return response()->json(["error" => $exception->getMessage()], 404);
        } elseif ($exception instanceof QueryException) {
            return response()->json(["error" => "Ошибка БД [{$exception->getCode()}]"], 500);
        } elseif ($exception instanceof ValidationException) {
            return response()->json(["error" => "Ошибка валидации запроса"], $exception->status);
        } elseif ($exception instanceof \TypeError) {
            $trace = $exception->getTrace();
            return response()->json([
                "error" => 'Неправильный тип: Класс - '.(current($trace)['class'] ?? 'Нет').' Функция - '.current($trace)['function']
            ],500);
        } elseif ($exception instanceof \InvalidArgumentException) {
            return response()->json(["error" => "Неправильный аргумент: {$exception->getMessage()}"], 500);
        } else {
            return response()->json(["error" => $exception->getMessage()], 500);
        }
    }
}
