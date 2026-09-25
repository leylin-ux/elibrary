<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = Session::get('locale', config('app.locale', 'km'));
        if (in_array($locale, ['en', 'km'])) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
