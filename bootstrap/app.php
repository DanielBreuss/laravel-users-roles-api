<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions){
        $exceptions->render(function (Exception $e, Request $request) {
            if (!$request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => 'Internal Server Error',
                    'message' => $e->getMessage(),
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            return match (true) {
                $e instanceof NotFoundHttpException || $e instanceof ModelNotFoundException => response()->json([
                    'success' => false,
                    'error' => 'Not Found',
                    'message' => $e->getMessage(),
                ], Response::HTTP_NOT_FOUND),

                $e instanceof AuthorizationException => response()->json([
                    'success' => false,
                    'error' => 'Forbidden',
                    'message' => $e->getMessage(),
                ], Response::HTTP_FORBIDDEN),

                $e instanceof AuthenticationException => response()->json([
                    'success' => false,
                    'error' => 'Unauthenticated',
                    'message' => $e->getMessage(),
                ], Response::HTTP_UNAUTHORIZED),

                $e instanceof ValidationException => response()->json([
                    'success' => false,
                    'error' => 'Validation Error',
                    'messages' => $e->errors(),
                ],Response::HTTP_UNPROCESSABLE_ENTITY),

                default => response()->json([
                    'success' => false,
                    'error' => 'Internal Server Error',
                    'message' => $e->getMessage(),
                ], Response::HTTP_INTERNAL_SERVER_ERROR),
            };
        });
    })
    ->create();
