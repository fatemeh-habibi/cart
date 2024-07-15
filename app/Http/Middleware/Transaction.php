<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Transaction
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
        \DB::beginTransaction();
        $response = $next($request);
        if ($response->exception) {
            \DB::rollBack();
        } else {
            \DB::commit();
        }

        return $response;
    }
}
