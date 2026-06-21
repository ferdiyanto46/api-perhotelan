<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;

class RoleMiddleware
{
    protected $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function handle($request, Closure $next, ...$roles)
    {
        if (!$this->auth->check()) {
            return response('Unauthorized', 401);
        }

        $user = $this->auth->user();
        
        foreach ($roles as $role) {
            if ($user->role === $role) {
                return $next($request);
            }
        }

        return response('Forbidden', 403);
    }
}