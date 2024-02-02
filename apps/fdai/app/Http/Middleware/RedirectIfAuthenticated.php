<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Middleware;

use App\Http\Urls\IntendedUrl;
use App\Logging\QMLog;
use App\Logging\QMLogLevel;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class RedirectIfAuthenticated
{
	/**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
		if(str_contains($request->getRequestUri(), 'logout')){
			return $next($request);
		}
		if(QMLogLevel::isDebug()){QMLog::logSession();}
	    $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $url = IntendedUrl::get();
                if($url){
	                $url = IntendedUrl::addUserInfoIfNecessary($url);
	                IntendedUrl::forget();
	                return redirect($url);
                }
            }
        }

        return $next($request);
    }
}
