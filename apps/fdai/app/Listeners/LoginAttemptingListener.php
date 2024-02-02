<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Listeners;
use App\Http\Urls\IntendedUrl;
use App\Logging\QMLog;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Login;
class LoginAttemptingListener
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
     * @param  Attempting  $event
     * @return void
     */
    public function handle(Attempting $event){
	    $intended = IntendedUrl::get();
	    QMLog::info("Attempting Login with guard $event->guard.  remember: $event->remember. credentials:".print_r
	                ($event->credentials, true)." with intended url: $intended");
    }
}
