<?php

namespace App\Exceptions;

use Throwable;
use App\Http\Controllers\Controller;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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

    public function render($request, Throwable $exception)
    {
        // Unauthenticated
        if ($exception instanceof AuthenticationException) {
            return Controller::error('غير مسموح بالدخول', 401);
        }

        // Not found
        if ($exception instanceof NotFoundHttpException) {
            return Controller::error('الرابط غير موجود', 404);
        }

        if ($exception instanceof ModelNotFoundException) {
            $model = class_basename($exception->getModel());
            return Controller::error("$model غير موجود", 404);
        }

        // Validation

  if ($exception instanceof ValidationException) {
            return Controller::error($exception->errors(), 422);
        }

        // HttpException
        if ($exception instanceof HttpException) {
            return Controller::error($exception->getMessage() ?: 'خطأ في الطلب', $exception->getStatusCode());
        }

        //role
        if ($exception instanceof \Spatie\Permission\Exceptions\UnauthorizedException) {
            return Controller::error('ليس لديك الصلاحية اللازمة للوصول إلى هذا المورد', 403);
        }
        // Authorization
       if ($exception instanceof AuthorizationException) {
        return Controller::error($exception->getMessage(), 403);

    }



        // Fallback
      //  return Controller::error('حدث خطأ ما', 500);

          return parent::render($request, $exception);
    }


}
