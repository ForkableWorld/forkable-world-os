<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Middleware;
use App\Buttons\Auth\AuthButton;
use App\Http\Urls\IntendedUrl;
use App\Logging\QMLog;
use App\Logging\QMLogLevel;
use App\Properties\Base\BaseClientSecretProperty;
use App\Slim\Middleware\QMAuth;
use Auth;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
/** Class Authenticate
 * @package App\Http\Middleware
 */
class QMAuthenticate extends Authenticate {
	public const NAME = 'auth';
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
	 * @throws AuthenticationException
	 */
	protected function authenticate($request, array $guards){
		QMAuth::logoutIfParamSet();
		if(QMLogLevel::isDebug()){QMLog::logSession();}
		try {
			parent::authenticate($request, $guards);
		} catch (AuthenticationException $e) {
			$user = QMAuth::getUser();
			if($user){
				Auth::setUser($user);
				QMLog::info("authenticate failed but $user is logged in!");
				return;
			}
			if($this->authenticationIsOptional($request)){
				QMLog::info("authenticate failed but authentication is optional");
				return;
			}
			throw $e;
		}
	}
	private function authenticationIsOptional(Request $request){
		$optional = false;
		if($request->is('*/connect')){$optional = true;}
		if($request->is('connect')){$optional = true;}
		if($request->is('*/connectors/list')){$optional = true;}
		if(BaseClientSecretProperty::fromRequest(false)){$optional = true;}
		return $optional;
	}
	/**
	 * Get the path the user should be redirected to when they are not authenticated.
	 * @param Request $request
	 * @return string
	 */
	protected function redirectTo($request): ?string{
		if(!AuthButton::shouldRedirectToLoginIfNotAuthenticated($request)){
			return null;
		}
		$url = url()->current();
		if(!IntendedUrl::validSet($url)){ // i.e. /broadcasting/auth
			return null;
		}
		$redirectResponse = AuthButton::getRedirect();
		return $redirectResponse->getTargetUrl();
	}
}
