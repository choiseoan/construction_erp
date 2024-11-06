<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use DB;

class BeforeMiddleware
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
        // Query 시간 체크를 위한 
        DB::enableQueryLog();
        
        // 프로그램 실행시간 체크용
        $request->startTime = microtime(1);

        return $next($request);
    }
}
