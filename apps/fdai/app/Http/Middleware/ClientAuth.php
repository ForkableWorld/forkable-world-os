<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Middleware;
use App\Buttons\Auth\AuthButton;
use App\Exceptions\InvalidClientException;
use App\Http\Urls\IntendedUrl;
use App\Models\OAClient;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
/** Class Authenticate
 * @package App\Http\Middleware
 */
class ClientAuth extends Authenticate {
	public const NAME = 'client:auth';
	/**
	 * Handle an incoming request.
	 * @param Request $request
	 * @param Closure $next
	 * @param mixed ...$guards
	 * @return mixed
	 * @throws AuthenticationException
	 */
	public function handle($request, Closure $next, ...$guards){
		if(AstralStaticsMiddleware::isStyleRequest()){
			//return $next($request);  // We need auth for script requests to add user to JS
		}
		return parent::handle($request, $next, $guards);
	}

    /**
     * @param Request $request
     * @param array $guards
     * @return void
     * @throws InvalidClientException
     */
	protected function authenticate($request, array $guards){
		OAClient::authorizeBySecret($request->input());
	}
	/**
	 * Get the path the user should be redirected to when they are not authenticated.
	 * @param Request $request
	 * @return string
	 */
	protected function redirectTo($request): ?string{
		if($request->ajax() || !$request->acceptsHtml()){
			return null;
		}
		if(!IntendedUrl::validSet(url()->current())){ // i.e. /broadcasting/auth
			return null;
		}
		return AuthButton::getRedirect()->getTargetUrl();
	}
}
