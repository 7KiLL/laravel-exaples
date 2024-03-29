<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
     * @param Exception $exception
     * @return void
     * @throws Exception
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Exception $exception
     * @return Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof ModelNotFoundException) {
            $response = [
                'message' => collect(preg_split('/\\\\/', $exception->getModel()))->last() . ' not found',
            ];
            if (!app()->environment('production')) {
                $response['debug'] = $exception->getTrace();
            }
            return response()->json(
                $response, 404);
        }
        if (app()->environment('production') &&
            app()->bound('sentry') &&
            $this->shouldReport($exception)) {
            app('sentry')->captureException($exception);
        }
        return parent::render($request, $exception);
    }


}
