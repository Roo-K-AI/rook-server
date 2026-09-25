<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

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
        $this->reportable(function (\Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     * @param  \Throwable  $e
     * @return Response
     */
    public function render($request, $e)
    {
        return parent::render($request, $e);
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param  Request  $request
     * @return JsonResponse|RedirectResponse
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json(['message' => 'Unauthenticated.'], 401);
    }

    /**
     * Check if the request is an API request
     */
    protected function isApiRequest(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }

    /**
     * Format the API response
     */
    protected function apiResponse(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Authentication required',
            'error' => [
                'code' => 'unauthenticated',
                'authenticate_url' => url('/api/auth/login'),
                'documentation' => url('/api/docs#authentication'),
            ],
        ], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Format the web response
     */
    protected function webResponse(): RedirectResponse
    {
        try {
            return redirect()->guest(
                Route::has('auth/login') ? route('auth/login') : '/login'
            );
        } catch (\Exception $e) {
            return redirect('/login');
        }
    }
}
