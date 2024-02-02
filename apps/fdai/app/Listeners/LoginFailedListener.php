<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Listeners;
use App\Http\Urls\IntendedUrl;
use App\Logging\QMLog;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
class LoginFailedListener
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
     * @param  Failed  $event
     * @return void
     */
    public function handle(Failed $event){
	    $intended = IntendedUrl::get();
	   QMLog::error("Login Failed for $event->user on guard $event->guard with credentials: ".
	                \App\Logging\QMLog::print_r($event->credentials, true)." with intended url: $intended");
    }
}
