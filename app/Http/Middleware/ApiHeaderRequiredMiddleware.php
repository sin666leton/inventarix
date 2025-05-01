<?php

namespace App\Http\Middleware;

use App\Exceptions\BadRequestException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiHeaderRequiredMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->header('accept') != 'application/json') return response()->json([
            'error' => 'Bad Request',
            'details' => "Header 'Accept: application/json' is required."
        ], 400);
        else return $next($request);
    }
}
