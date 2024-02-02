<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Listeners;
use Analytics;
use App\Http\Middleware\EncryptCookies;
use App\Http\Urls\IntendedUrl;
use App\Logging\QMLog;
use App\Models\User;
use App\Properties\Base\BaseTimezoneProperty;
use App\UI\Alerter;
use App\Utils\QMCookie;
use Illuminate\Auth\Events\Authenticated;
/**
 * @package App\Listeners
 * Called AFTER Auth:user() is set
 */
class AuthenticatedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
	/**
	 * Handle the event.
	 * @param Authenticated $event
	 * @return void
	 */
    public function handle(Authenticated $event){
	    /** @var User $u */
	    $u = $event->user;
	    //QMLog::info(static::class.": Authenticated $event->user with guard: $event->guard with intended url
	    // $intended");
	    $this->setTimeZone($u);
	    $this->setWebLoginCookieForSlim($u);
	    Analytics::setUserId($u->getId());
    }
	/**
	 * @param User $u
	 */
	private function setWebLoginCookieForSlim(User $u): void{
		$g = auth()->guard('web');
		QMCookie::setCookie($g->getName(), $u->getAuthIdentifier());
		// Cookie::queue doesn't seem to work for some reason but the setUser cookie seems to work
		// Cookie::queue(QMCookie::getLoggedInCookieName(), $qmUser->getLoggedInCookieValue(), QMCookie::getCookieLifetimeDurationInMinutes());
	}
	/**
	 * @param User $u
	 */
	private function setTimeZone(User $u): void{
		if($tz = BaseTimezoneProperty::fromRequest()){
			try {
				$u->setTimeZone($tz);
			} catch (\Throwable $e) {
			    QMLog::error("could not set timezone to $tz for user $u->name because: $e");
			}
		}
	}
	/**
	 * @param User $u
	 */
	private function sayHi(User $u): void{
		$m = 'Hello ' . $u->name . ', welcome back!';
		Alerter::toast($m);
	}
}
