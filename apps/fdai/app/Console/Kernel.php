<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Console;
use App\Console\Commands\DumpCredentials;
use App\Console\Commands\ExportMeasurements;
use App\Console\Commands\GenerateBigSiteMap;
use App\Console\Commands\PredictorEmails;
use App\Console\Commands\TrackingReminderNotificationsEmailCommand;
use App\Logging\ArtisanLogger;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Throwable;
class Kernel extends ConsoleKernel {
	protected function bootstrappers(): array{   // https://docs.bugsnag.com/platforms/php/laravel/#reporting-out-of-memory-exceptions
		return array_merge(
			[\Bugsnag\BugsnagLaravel\OomBootstrapper::class],
			parent::bootstrappers(),
		);
	}
    /**
     * The Artisan commands provided by your application.
     * @var array
     */
    protected $commands = [
        ExportMeasurements::class,
        PredictorEmails::class,
        DumpCredentials::class,
        TrackingReminderNotificationsEmailCommand::class,
        GenerateBigSiteMap::class,
    ];
    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void{
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
	/**
	 * Define the application's command schedule.
	 * @param  \Illuminate\Console\Scheduling\Schedule $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule): void{
		$schedule->command('inspire')->hourly();
		$schedule->command('telescope:prune')->daily();
		$schedule->command('measurements:export')->hourly();
	}
	/**
	 * @return \App\Console\Kernel
	 */
	public static function console(): Kernel{
    	return app(self::class);
    }
	/**
	 * @param string $command
	 * @param array $parameters
	 * @param null $outputBuffer
	 * @return int
	 */
	public function call($command, array $parameters = [], $outputBuffer = null): int{
	    try {
		    return parent::call($command, $parameters, $outputBuffer);
	    } catch (Throwable $e) {
		    if(stripos($e->getMessage(), "argument does not exist") !== false){
			    $withDashes = self::addDashesToParams($parameters);
			    return parent::call($command, $withDashes, $outputBuffer);
		    } else{
			    /** @var \LogicException $e */
			    throw $e;
		    }
	    }
    }

    /**
     * // If a argument is unnamed look up the command and find what it's really called.
     * i.e. vendor/crestapps/laravel-code-generator/src/Commands/CreateResourcesCommand.php has model-name at the top of the list
     * include argument in $parameters array with "whatever_you_found" => "arg value" (no dashes before argument name"
     * @param string $command
     * @param array $parameters
     * @param null $outputBuffer
     * @return CommandFinished command output
     */
	public static function artisan(string $command, array $parameters = [], $outputBuffer = null): CommandFinished {
		//QMLog::logStartOfProcess("artisan $command"); // Logging is handled in \App\Listeners\LogCommandStarting
		$console = self::console();
		$parameters["--verbose"] = 1;
		$parameters["--no-interaction"] = 1;
		$exitCode = $console->call($command, $parameters, $outputBuffer);
		$event = ArtisanLogger::getLastCommandFinished();
		//QMLog::info($console->output()); // Logging is handled in \App\Listeners\LogArtisanCommandFinishedOutput
		if($exitCode !== 0){
			$output =  $console->output();
			$output = $event->output->fetch();
			le("Got exit code $exitCode from $command! \n\tOutput:\n" .$output);
		}
		return $event;
	}
	/**
	 * @param array $parameters
	 * @return array
	 */
	private static function addDashesToParams(array $parameters): array{
		$withDashes = [];
		foreach($parameters as $key => $value){
			if(stripos($key, "-") !== 0){
				$withDashes["--$key"] = $value;
			} else{
				$withDashes[$key] = $value;
			}
		}
		return $withDashes;
	}
}
