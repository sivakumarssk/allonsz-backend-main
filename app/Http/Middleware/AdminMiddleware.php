<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use \Auth;

class AdminMiddleware
{

    public function handle($request, Closure $next)
    {
        if(Auth::User() && Auth::User()->role == 'admin'){
            return $next($request);
        }
        return redirect('/');
    }
}
