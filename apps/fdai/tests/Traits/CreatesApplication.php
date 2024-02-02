<?php
namespace Tests\Traits;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Storage\DB\Writable;
use App\Storage\QMFileCache;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;
trait CreatesApplication
{
	/**
	 * @return Application
	 */
    public static function bootstrapApp(): Application{
	    ConsoleLog::debug(__METHOD__);
	    /** @var Application $app */
	    $app = require __DIR__ . '/../../bootstrap/app.php';
	    $app->make(Kernel::class)->bootstrap();
		$file = $app->environmentFilePath();
		ConsoleLog::debug("Currently using env from: $file");
        return $app;
    }
    /**
     * Creates the application.
     * @return Application
     */
    public function createApplication(): Application{
		ConsoleLog::debug(__METHOD__);
        $app = self::bootstrapApp();
	    $this->validateDB();
	    self::clearCache();
	    return $this->app = $app;
    }
    /**
     * Clears Laravel Cache.
     */
    public static function clearCache(){
	    ConsoleLog::debug(__METHOD__);
        $commands = [
//			'clear-compiled',
//	        'cache:clear',
//	        'view:clear',
//	        'config:clear',
//	        'route:clear'
        ];
        foreach ($commands as $command) {
            Artisan::call($command);
        }
	    //QMClockwork::clean();
	    // This didn't solve the slow Debugbar issue: LaravelLogFile::delete(__FUNCTION__);
	    QMFileCache::clear();
    }
	abstract protected function getAllowedDBNames():array;
	protected function validateDB(): void{
		$name = Writable::getDbName();
		if(str_contains($name, 'test')){
			return;
		}
		$allowed = $this->getAllowedDBNames();
        $allowed[] = ':memory:';
		$test = $this->getName();
		try {
			$schema = Writable::getSchemaName();
			if(str_contains($schema, 'test')){
				return;
			}
		} catch (\Throwable $e){
		    QMLog::debug("Not using postgresql");
		}
		if($name && !in_array($name, $allowed)){le("DB $name not allowed for $test!", ['allowed' => $allowed]);}
	}
}
