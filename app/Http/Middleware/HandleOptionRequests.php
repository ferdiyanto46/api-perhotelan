<?php

namespace App\Middleware;

use Closure;

class HandleOptionRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Jika request adalah OPTIONS (Preflight dari browser)
        if ($request->isMethod('OPTIONS')) {
            return response()->json('OK', 200, [
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS, PUT, DELETE',
                'Access-Control-Allow-Headers' => 'Content-Type, Accept, Authorization, X-Requested-With, Origin, Application'
            ]);
        }

        // Teruskan request selain OPTIONS
        $response = $next($request);

        // Tambahkan header CORS ke semua response
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PUT, DELETE');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Origin, Application');

        return $response;
    }
}