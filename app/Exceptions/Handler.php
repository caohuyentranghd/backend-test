<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Http\Response;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
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
        $result = [
            'status', 'message', 'errors' => []
        ];

        if ($request->is('api/*')) {
            switch (true) {
                case $exception instanceof NotFoundHttpException:
                    $result['status'] = Response::HTTP_NOT_FOUND;
                    $result['message'] = __('common.msg_not_found');
                    break;
                case $exception instanceof \ErrorException:
                    $result['status'] = Response::HTTP_INTERNAL_SERVER_ERROR;
                    $result['message'] =  __('common.msg_failure_handling');
                    break;
                case $exception instanceof ValidationException:
                    $result['status'] = Response::HTTP_BAD_REQUEST;
                    $result['message'] = __('common.msg_valid_fails');
                    $result['errors'] = $exception->validator->getMessageBag()->toArray();
                    break;
                case $exception instanceof AuthenticationException:
                    $result['status'] = Response::HTTP_FORBIDDEN;
                    $result['message'] =  __('common.msg_auth_token_error');
                    break;
                default:
                    $result['status'] = Response::HTTP_NOT_ACCEPTABLE;
                    $result['message'] =  __('common.msg_failure_handling');
                    break;
            }
            
            return $this->failResponse($result['status'], $result['message'], $result['errors']);
        }

        return parent::render($request, $exception);
    }

    /**
     * @param  $code
     * @param string $message
     * @param array $error
     * 
     * @return mixed
     */
    private function failResponse($status, $message = '', $error = [])
    {
        return response()->json([
            'success' => false,
            'error' => $error,
            'message' => $message,
            'code' => $status
        ], $status);
    }
}
