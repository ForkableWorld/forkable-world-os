<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Middleware;
use App\Buttons\Auth\LoginButton;
use App\Models\User;
use App\Slim\Middleware\QMAuth;
use Closure;
use Illuminate\Http\Request;
class AdminMiddleware {
	const NAME = 'admin';
	/**
	 * Handle an incoming request.
	 * @param Request $request
	 * @param Closure $next
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next): mixed{
		/** @var User $u */
		$u = QMAuth::getUser();
		if(!$u){
			$u = $request->user();
		}
		if(!$u){
			if(!$request->acceptsHtml() || $request->ajax()){
				return response("Please provide access token.", 401);
			}
			return LoginButton::getRedirect();
		}
		if(!$u->isAdmin()){
			$message = 'Insufficient Permissions';
			$u->logout($message);
			if(!$request->acceptsHtml() || $request->ajax()){
				return response($message, 401);
			}
			return LoginButton::getRedirect([
                'logout' => true,
                'message' => $message,
            ]);
		}
		return $next($request);
	}
}
