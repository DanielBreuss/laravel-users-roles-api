<?php

namespace App\Exceptions;

use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpFoundation\Response;

class Handler extends ExceptionHandler
{
    public function __construct(Container $container)
    {
        parent::__construct($container);
    }
    /**
     * Список исключений, которые не нужно логировать.
     */
    protected $dontReport = [
        //
    ];

    /**
     * Список полей, которые не нужно "флэшить" при валидации.
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function report(Throwable $exception): void
    {
        // Можно кастомно логировать исключения
        // Например, отправлять в Sentry или Log::error()
        parent::report($exception);
    }

    /**
     * Рендер исключений в HTTP-ответы.
     */
    public function render($request,Throwable $e)
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            // Модель не найдена
            if ($e instanceof ModelNotFoundException) {
                return response()->json(['error' => 'Resource not found'], 404);
            }

            // Роут не найден
            if ($e instanceof NotFoundHttpException) {
                return response()->json(['error' => 'Endpoint not found'], 404);
            }

            // Запрещено действие (политики)
            if ($e instanceof AuthorizationException) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            // Ошибки валидации
            if ($e instanceof ValidationException) {
                return response()->json([
                    'error' => 'Validation failed',
                    'messages' => $e->errors(),
                ], 422);
            }

            // Любое другое исключение
            return response()->json([
                'error' => 'Server Error',
                'message' => $e->getMessage(),
            ], 500);
        }

        // Для веб-запросов оставляем стандартный рендер
        return parent::render($request, $e);
    }
}
