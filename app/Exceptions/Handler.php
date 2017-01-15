<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Exceptions;

use Auth;
use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        ModelNotFoundException::class,
        NotFoundHttpException::class,
        ValidationException::class,
        HttpException::class,
    ];

    protected $exceptionAliases = [
        ModelNotFoundException::class   => ['EntityNotFound', 'The requested entity was not found.'],
        NotFoundHttpException::class    => ['EndpointNotFound', 'The requested endpoint was not found.'],
        HttpException::class            => ['RequestError', 'An error occurred while performing the request.'],
    ];

    protected $httpExceptionMessages = [
        400 => 'Your request cannot be processed',
        401 => 'You are not authorized to access this resource',
        403 => 'You have no permission to access this resource',
        404 => 'The requested endpoint was not found.',
        405 => 'Unsupported request',
        500 => 'An internal server error occurred'
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        if (!in_array(get_class($e), $this->dontReport)) {
            if (!config('app.debug') && config('bugsnag.api_key') && app()->bound('bugsnag')) {
                if ($user = Auth::user()) {
                    app('bugsnag')->setUser([
                        'id'    => $user->getAuthIdentifier(),
                        'name'  => $user->name
                    ]);
                }

                app('bugsnag')->notifyException($e, null, 'error');
            }
        }

        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($request->ajax() || strpos($request->getPathinfo(), '/api') === 0) {
            if (app()->environment() == 'production') {
                return $this->renderJson($request, $e);
            }
        }

        if ($this->isHttpException($e)) {
            return $this->renderHttpException($e);
        }

        if (app()->environment() == 'production') {
            if ($e instanceof TokenMismatchException) {
                return response()->view('errors.403', [], 403);
            }

            return response()->view('errors.500', [], 500);
        }

        return parent::render($request, $e);
    }

    /**
     * Render JSON response
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    private function renderJson($request, Exception $e)
    {
        $fullClassName = get_class($e);

        $error = [
            'code'      => 500,
            'type'      => $fullClassName,
            'message'   => 'Error'
        ];

        if (array_key_exists($fullClassName, $this->exceptionAliases)) {
            $error['type'] = $this->exceptionAliases[$fullClassName][0];
            $error['message'] = $this->exceptionAliases[$fullClassName][1];
        } else {
            $error['type'] = (new \ReflectionClass($e))->getShortName();
        }

        if ($e instanceof ModelNotFoundException) {
            $error['code'] = 404;
        }

        if ($e instanceof HttpException) {
            $error['code'] = $e->getStatusCode();

            if (array_key_exists($e->getStatusCode(), $this->httpExceptionMessages)) {
                $error['message'] = $this->httpExceptionMessages[$e->getStatusCode()];
            }
        }

        return response()->json(['error' => $error], $error['code'], [], JSON_PRETTY_PRINT);
    }
}
