<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Listeners;
use App\Logging\ArtisanLogger;
use Illuminate\Console\Events\CommandStarting;
class LogArtisanStarting
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
	 * @param \Illuminate\Console\Events\CommandStarting $event
	 * @return void
	 */
    public function handle(CommandStarting $event){
    	ArtisanLogger::logStarting($event);
    }
}
