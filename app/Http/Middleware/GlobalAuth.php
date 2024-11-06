<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
//use Func;

class GlobalAuth
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
        // Log::Debug("= ".print_r(Auth::user(),true));
        if( Auth::check() || $request->is("auth/login") || $request->is("/") )
        {
            //echo Func::chkPermit($request->path());
            //Log::Debug("REQUEST PATH = ".$request->path());

            // 권한 체크
            return $next($request);
        }
        else
        {
            Log::Debug("BANNED ACCESS = ".$request->path());
            return redirect('/');
        }
    }

}
