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
        HttpException::class,
        NotFoundHttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
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
        if (!config('app.debug') && config('bugsnag.api_key') && app()->bound('bugsnag')) {
            if ($user = Auth::user()) {
                app('bugsnag')->setUser([
                    'id'    => $user->getAuthIdentifier(),
                    'name'  => $user->name
                ]);
            }

            app('bugsnag')->notifyException($e, null, 'error');
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
        if ($this->isHttpException($e)) {
            return $this->renderHttpException($e);
        } else if (app()->environment() == 'production') {
            return response()->view('errors.500', [], 500);
        }

        if ($request->ajax() || strpos($request->getPathinfo(), '/api/') === 0) {
            if ($e instanceof ModelNotFoundException) {
                return response()->json([
                    'error_message'   => 'The entity you requested was not found'
                ], 404);
            }

            if ($e instanceof HttpException && $e->getStatusCode() == 403) {
                return response()->json([
                    'error_message'   => 'You have no permission to access this entity'
                ], 403);
            }
        }

        return parent::render($request, $e);
    }
}
