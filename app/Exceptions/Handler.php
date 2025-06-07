<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Handle 404 errors specifically for profile routes
        if ($exception instanceof NotFoundHttpException) {
            // Check if the request is for a profile route
            if ($request->is('profile/*')) {
                return response()->view('errors.user-not-found', [], 404);
            }

            // For other 404s, you can customize as needed
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Halaman tidak ditemukan'
                ], 404);
            }

            // You can create a general 404 page as well
            return response()->view('errors.404', [], 404);
        }

        return parent::render($request, $exception);
    }
}
