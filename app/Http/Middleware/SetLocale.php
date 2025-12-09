<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, \Closure $next)
    {
        $path = $request->path();
        $locale = 'en'; // default
        
        if (str_starts_with($path, 'de/')) {
            $locale = 'de';
        } elseif (str_starts_with($path, 'en/')) {
            $locale = 'en';
        }
        
        app()->setLocale($locale);
        return $next($request);
    }
}
