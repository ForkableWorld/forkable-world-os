<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Http\Middleware;
use App\Slim\View\Request\QMRequest;
use App\Utils\QMProfile;
use Closure;
use Illuminate\Http\Request;
class ProfileMiddleware {
	/**
	 * Handle an incoming request.
	 * @param Request $request
	 * @param \Closure $next
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next){
		if(!QMRequest::urlContains("_clockwork")){
			QMProfile::endProfile();
			QMProfile::profileIfEnvSet(false, false,\request()->method()." ".url()->current());
		}
		return $next($request);
	}
}
