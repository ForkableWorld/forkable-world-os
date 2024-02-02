<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Middleware;
use App\Slim\Middleware\QMAuth;
use Closure;
use Illuminate\Http\Request;
/** Class Authenticate
 * @package App\Http\Middleware
 */
class LogoutMiddleware {
	/**
	 * Handle an incoming request.
	 * @param Request $request
	 * @param Closure $next
	 * @param mixed ...$guards
	 * @return mixed
	 */
	public function handle($request, Closure $next){
		QMAuth::logoutIfParamSet();
		return $next($request);
	}
}
