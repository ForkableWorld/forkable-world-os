<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Code;
use App\Console\Kernel;
use App\Exceptions\ExceptionHandler;
use App\Files\PHP\BaseModelFile;
use App\Models\GlobalVariableRelationship;
use App\Models\Application;
use App\Models\BaseModel;
use App\Models\Collaborator;
use App\Models\CommonTag;
use App\Models\Connection;
use App\Models\Connector;
use App\Models\ConnectorImport;
use App\Models\ConnectorRequest;
use App\Models\UserVariableRelationship;
use App\Models\DeviceToken;
use App\Models\Measurement;
use App\Models\MeasurementExport;
use App\Models\MeasurementImport;
use App\Models\OAAccessToken;
use App\Models\OAClient;
use App\Models\Purchase;
use App\Models\SentEmail;
use App\Models\Study;
use App\Models\Subscription;
use App\Models\TrackingReminder;
use App\Models\TrackingReminderNotification;
use App\Models\Unit;
use App\Models\UnitCategory;
use App\Models\User;
use App\Models\UserTag;
use App\Models\UserVariable;
use App\Models\UserVariableClient;
use App\Models\Variable;
use App\Models\VariableCategory;
use App\Models\Vote;
use App\Models\WpPost;
use App\PhpUnitJobs\JobTestCase;
use App\Types\QMStr;
class ScaffoldJob extends JobTestCase {
	public static function generateModelScaffoldAndUpdatePHPDocs(string $table){
		$class = BaseModel::getClassByTable($table);
		$class::generateScaffold();
	}
	public function testGenerateScaffolds(){
		UnitCategory::generateScaffold();
		Unit::generateScaffold();
		DeviceToken::generateScaffold();
		TrackingReminder::generateAPIControllers();
		OAAccessToken::generateScaffold();
		OAClient::generateScaffold();
		ConnectorImport::generateScaffold();
		ConnectorRequest::generateScaffold();
		Connection::generateScaffold();
		TrackingReminderNotification::generateScaffold();
		return;
		Connector::generateScaffold();
		User::generateScaffold();
		GlobalVariableRelationship::generateScaffold();
		Application::generateScaffold();
		Collaborator::generateScaffold();
		CommonTag::generateScaffold();
		ConnectorImport::generateScaffold();
		UserVariableRelationship::generateScaffold();
		Measurement::generateScaffold();
		MeasurementExport::generateScaffold();
		MeasurementImport::generateScaffold();
		Notification::generateScaffold();
		Purchase::generateScaffold();
		SentEmail::generateScaffold();
		Study::generateScaffold();
		Subscription::generateScaffold();
		UserTag::generateScaffold();
		UserVariable::generateScaffold();
		Variable::generateScaffold();
		VariableCategory::generateScaffold();
		UserVariableClient::generateScaffold();
		Vote::generateScaffold();
		WpPost::generateScaffold();
		$interesting = BaseModel::getInterestingModelClasses();
		foreach($interesting as $class){
			if($infyOm = true){
				self::infyomGenerateScaffoldFromTable($class);
			}else{
				BaseModelFile::generateResources($class);
			}
		}
	}
	/**
	 * @param string $class
	 * @param string|null $table
	 */
	public static function infyomGenerateScaffoldFromTable(string $class, string $table = null): void{
		$overwrite = config('infyom.laravel_generator.overwrite', false);
		if(!$table){$table = QMStr::classToTableName($class);}
		try {
			Kernel::artisan("infyom:api_scaffold", [
				'model' => $class,
				'--tableName' => $table,
				'--fromTable' => true,
				'--skip' => 'dump-autoload'
			]);
		} catch (\Throwable $e) {
			ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
		}
	}
}
