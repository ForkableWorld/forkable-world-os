<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class SetGetFromRequest {
	/**
	 * Need this for tests
	 * @param Request $request
	 * @param \Closure $next
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next){
		$q = $request->query;
		$_GET = $q->all();
		return $next($request);
	}
}
