<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Listeners;
use App\Logging\QMLog;
use Illuminate\Auth\Events\Logout;
class LogoutListener
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
     * @param  Logout  $event
     * @return void
     */
    public function handle(Logout $event){
	    QMLog::info("Logged out $event->user from guard $event->guard and probably cycled remember token");
    }
}
