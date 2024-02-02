<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Listeners;
use App\Logging\ArtisanLogger;
use Illuminate\Console\Events\CommandFinished;
class LogArtisanFinished
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
	 * @param \Illuminate\Console\Events\CommandFinished $event
	 * @return void
	 */
    public function handle(CommandFinished $event): void {
    	ArtisanLogger::logFinished($event);
    }
}
