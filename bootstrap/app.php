<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null; // let the default handler handle non-api routes
            }

            // Default values
            $message = 'An error occurred';
            $errors = [];
            $status = 500;

            // Validation errors (422)
            if ($e instanceof ValidationException) {
                /** @var ValidationException $e */
                $message = 'Validation failed';
                $errors = $e->errors();
                $status = 422;
                return response()->json(compact('message', 'errors', 'status'), $status);
            }

            // Method not allowed (405)
            if ($e instanceof MethodNotAllowedHttpException) {
                $message = 'Method not allowed';
                $errors = [];
                $status = 405;
                return response()->json(compact('message', 'errors', 'status'), $status);
            }

            // Model not found -> Resource not found (404)
            if ($e instanceof ModelNotFoundException) {
                $message = 'Resource not found';
                $errors = [];
                $status = 404;
                return response()->json(compact('message', 'errors', 'status'), $status);
            }

            // Route/endpoint not found (404)
            if ($e instanceof NotFoundHttpException) {
                $message = 'Endpoint not found';
                $errors = [];
                $status = 404;
                return response()->json(compact('message', 'errors', 'status'), $status);
            }

            // Generic HTTP exception - use provided status/message when possible
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $status = $e->getStatusCode();
                $message = $e->getMessage() ?: 'HTTP error';
                $errors = [];
                return response()->json(compact('message', 'errors', 'status'), $status);
            }

            // Fallback - let default handler process it (this will typically generate a 500)
            return null;
        });
    })->create();
