<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Middleware;
use App\Utils\QMCookie;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Session\Store;
class QMStartSession extends StartSession
{
	/**
	 * @param \Illuminate\Session\Store $session
	 */
	protected static function populateSessionFromCookies(Store $session): void{
		foreach(QMCookie::all() as $key => $value){
			$existing = $session->get($key);
			if(!$existing){
				$session->put($key, $value);
			}
		}
	}
	/**
	 * Start the session for the given request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Contracts\Session\Session
	 */
	public function startSession(Request $request, $session): Session{
		$session = $this->getSession($request);
        return tap($session, function (Store $session) use ($request) {
	        $session->setRequestOnHandler($request);
	        $session->start();
	        QMStartSession::populateSessionFromCookies($session);
        });
    }
}
