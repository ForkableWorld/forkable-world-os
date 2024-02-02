<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Middleware;
use Closure;
use Exception;
use Illuminate\Http\Request;
class AstralStaticsMiddleware {
    /**
     * Handle an incoming request.
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws Exception
     * @noinspection PhpMissingParamTypeInspection
     */
	public function handle($request, Closure $next){
		if($request->getMethod() === 'GET'){
			if(self::isStaticsRequest()){
				return app(SetCacheControl::class)->handle($request, static function($request) use ($next){
						return $next($request);
					}, 'private;max_age=3600;etag');
			}
		}
		return $next($request);
	}
	/**
	 * @return bool
	 */
	public static function isStaticsRequest():bool{
		return self::isScriptRequest() || self::isStyleRequest();
	}
    /**
     * @return bool
     */
    public static function isStyleRequest():bool{
        if(\request()->getMethod() === 'GET'){
            $uri = \request()->getRequestUri();
            if(strpos($uri, 'astral-api/styles')){
                return true;
            }
        }
        return false;
    }
    /**
     * @return bool
     */
    public static function isScriptRequest():bool{
        if(\request()->getMethod() === 'GET'){
            $uri = \request()->getRequestUri();
            if(strpos($uri, 'astral-api/scripts')){
                return true;
            }
        }
        return false;
    }
}
