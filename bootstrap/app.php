<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, \Illuminate\Http\Request $request) {
            if ($e->getStatusCode() === 419) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'message' => 'CSRF token mismatch. Please reload the page.',
                    ], 419);
                }

                return redirect()->route('login')
                    ->withInput($request->except('password', '_token'))
                    ->with('warning', app()->getLocale() === 'km'
                        ? 'សម័យការងារ (Session) ត្រូវបានផុតកំណត់ដោយសារទុកចោលយូរ។ សូមព្យាយាមចូលប្រព័ន្ធម្ដងទៀត។'
                        : 'Your session has expired due to inactivity. Please sign in again.');
            }
        });
    })->create();
