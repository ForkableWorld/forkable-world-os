<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Code;
use App\Console\Kernel;
use App\Exceptions\ExceptionHandler;
use App\Files\FileFinder;
use App\PhpUnitJobs\JobTestCase;
use App\Types\QMStr;
class ControllersJob extends JobTestCase {
	/**
	 * @param string $model
	 * @param string|null $table
	 */
	public static function infyomGenerateAPIFromTable(string $model, string $table = null): void{
		$overwrite = config('infyom.laravel_generator.overwrite', false);
		if(!$table){$table = QMStr::classToTableName($model);}
		try {
			Kernel::artisan("infyom:api", [
				'model' => $model,
				'--tableName' => $table,
				'--fromTable' => true,
				'--skip' => 'dump-autoload'
			]);
		} catch (\Throwable $e) {
			ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
		}
	}
	public function testReGenerateAPIControllers(){
		$files = FileFinder::listFiles('app/Http/Controllers/API');
		foreach($files as $file){
			$table = QMStr::snakize(QMStr::between($file, 'API/', 'APIController'));
			$plural = QMStr::pluralize($table);
			self::infyomGenerateAPIFromTable($plural);
		}
	}
}
