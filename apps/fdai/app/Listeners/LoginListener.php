<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Listeners;
use App\Http\Urls\IntendedUrl;
use App\Logging\QMLog;
use App\Models\User;
use Illuminate\Auth\Events\Login;
class LoginListener
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
     *
     * @param  Login  $event
     * @return void
     * Called BEFORE Auth:user() is set.  See AuthenticatedListener for after Auth:user() is set.
     */
    public function handle(Login $event){
	    /** @var User $u */
	    $u = $event->user;
	    // Don't do IntendedUrl::get(); because it removes it from the session
	    // $intended = IntendedUrl::get();
	    QMLog::info(static::class.": Logging in $event->user from guard $event->guard with");
    }
}
