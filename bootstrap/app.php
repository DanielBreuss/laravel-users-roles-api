<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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
                ], 500);
            }
            return match (true) {
                $e instanceof NotFoundHttpException || $e instanceof ModelNotFoundException => response()->json([
                    'success' => false,
                    'error' => 'Not Found',
                    'message' => $e->getMessage(),
                ], 404),

                $e instanceof AuthorizationException => response()->json([
                    'success' => false,
                    'error' => 'Forbidden',
                    'message' => $e->getMessage(),
                ], 403),

                $e instanceof ValidationException => response()->json([
                    'success' => false,
                    'error' => 'Validation Error',
                    'messages' => $e->errors(),
                ],422),

                default => response()->json([
                    'success' => false,
                    'error' => 'Internal Server Error',
                    'message' => $e->getMessage(),
                ], 500),
            };
        });
    })
    ->create();
