<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AddJsonHeaderToRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        $request->headers->set("accept","application/json");


        return $next($request);
    }
}
