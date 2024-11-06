<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use DB;
use Log;

class AfterMiddleware
{

    // Query 체크할 시간(microtime)
    private $qTime = 1000;

    // 프로그램 실행시간 체크할 시간(초)
    private $pTime = 1;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Perform action

        // debug 할때 사용
        if(isset($request->isDebug) && $request->isDebug==true)
        {
            Log::debug(DB::getQueryLog());
        }

        // 시간이 오래걸리는 쿼리를 찾아서 로그로 남긴다
        $query = DB::getQueryLog();
        if(isset($query))
        {
            foreach($query as $q)
            {
                if($q['time']>$this->qTime)
                {
                    Log::alert($q);
                }
            }
        }

        $chkTime = microtime(1) - $request->startTime;
        if($chkTime>$this->pTime)
        {
            Log::alert('프로그램 실행시간 : '.$chkTime);
        }

        return $response;
    }
}
