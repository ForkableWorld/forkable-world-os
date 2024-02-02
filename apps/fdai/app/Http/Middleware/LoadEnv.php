<?php namespace App\Http\Middleware;
use App\Utils\Env;
use Closure;
use Illuminate\Http\Request;
class LoadEnv {
	/**
	 * Handle an incoming request.
	 * @param Request $request
	 * @param Closure $next
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next): mixed{
		Env::loadEnvIfNoAppUrl();
		if(!Env::getAppUrl()){
			le('App URL not set');
		}
		return  $next($request);
	}
}
