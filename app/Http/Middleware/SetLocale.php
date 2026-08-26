<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header('Accept-Language') 
            ?? $request->header('lang') 
            ?? $request->get('lang') 
            ?? config('app.locale', 'ar');

        // Extract primary language code (e.g. 'en-US' => 'en')
        if (is_string($locale)) {
            $locale = substr($locale, 0, 2);
        }

        if (in_array($locale, ['ar', 'en'])) {
            app()->setLocale($locale);
        } else {
            app()->setLocale('ar'); // Default fallback
        }

        return $next($request);
    }
}
