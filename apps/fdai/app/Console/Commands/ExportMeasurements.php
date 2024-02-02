<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

//USAGE: `php artisan measurements:export ETERNAL`
//DEBUG: `/vagrant/cli_debug.sh /vagrant/laravel/artisan measurements:export ETERNAL`
namespace App\Console\Commands;
use App\Logging\QMLog;
use Illuminate\Console\Command;
use App\Slim\Model\Measurement\MeasurementExportRequest;
class ExportMeasurements extends Command {
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'measurements:export {loop=ETERNAL}';
    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Export measurements.';
    public function handle(){
        $loop = $this->argument("loop");
        if($loop === "DEBUG"){
            putenv("DEBUG_MODE=1");
        }
        while(true){
            $success = MeasurementExportRequest::sendExportedMeasurementsForFirstWaitingOrStuckRequest();
            QMLog::info("sendMeasurements finished.");
            if($loop !== "ETERNAL"){
	            QMLog::info("No eternal param so ending task");
                echo "No eternal param so ending task\n";
                die(0);
            }
            $sleepSeconds = 300;
            $message = "Parameter is ".$loop." so sleeping $sleepSeconds seconds before checking for more export requests.";
	        QMLog::info($message);
            sleep($sleepSeconds);
        }
    }
}
