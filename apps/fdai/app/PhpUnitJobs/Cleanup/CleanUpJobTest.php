<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Cleanup;
use App\Buttons\QMButton;
use App\Console\QMCommands;
use App\Files\FileFinder;
use App\Files\FileHelper;
use App\Astral\Actions\DeleteTestUsersAction;
use App\Properties\Connection\ConnectionNumberOfMeasurementsProperty;
use App\Properties\Measurement\MeasurementConnectionIdProperty;
use App\Properties\User\UserNumberOfPatientsProperty;
use App\Properties\UserVariable\UserVariableNumberOfUserVariableRelationshipsAsEffectProperty;
use App\Properties\Variable\VariableClientIdProperty;
use App\Properties\Variable\VariableIsPublicProperty;
use App\Storage\DB\Writable;
use App\Storage\Firebase\FirebaseGlobalTemp;
use App\Storage\S3\S3Private;
use App\Types\QMStr;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\PhpUnitJobs\JobTestCase;
use App\Utils\SecretHelper;
use App\Files\PHP\BasePhpUnitTestFile;
/** @package App\PhpUnitJobs
 */
class CleanUpJobTest extends JobTestCase {
    public function testClearCache(){
        QMCommands::clearCache();
    }
    public function testReplaceSecrets(){
        SecretHelper::replaceSecretsInFiles();
    }
	public function testCleanUpTests(){
		BasePhpUnitTestFile::clean();
	}
    public function testCleanUpJob(){
	    FirebaseGlobalTemp::deleteTemp();
	    FirebaseGlobalTemp::clearTempIfOlderThan24Hours();
    	S3Private::deleteSecretFiles();
        VariableIsPublicProperty::updateAll();
        VariableClientIdProperty::updateAll();
        UserVariableNumberOfUserVariableRelationshipsAsEffectProperty::updateAll();
        MeasurementConnectionIdProperty::fixNulls();
        ConnectionNumberOfMeasurementsProperty::updateAll();
        UserNumberOfPatientsProperty::updateAll();
        DeleteTestUsersAction::deleteOldTestUsers();
        self::pruneTelescopeTable();
        $this->deleteOldAPITrackerLogData();
        TrackingReminderCleanupJobTest::deleteNotificationsForNonExistentReminders();
        QMTrackingReminderNotification::deleteOldNotifications();
    }
    private function deleteOldAPITrackerLogData(){
        $oneMonthAgo = date('Y-m-d H:i:s', time() - 86400 * 30);
        $db = Writable::db();
        $db->table('tracker_log')->where('updated_at', '<', $oneMonthAgo)->delete();
        $db->table('tracker_sessions')->where('updated_at', '<', $oneMonthAgo)->delete();
    }
    public static function pruneTelescopeTable(){
        QMCommands::pruneTelescopeTable();
    }
    public static function generateAdminControllers(): void{
        $files = FileFinder::listFilesRecursively('app/Http/Controllers/Admin');
        $contents = "<?php
namespace App\Http\Controllers\Admin;
use App\DataTables\VoteDataTable;
use App\Http\Controllers\BaseAdminController;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;
use Response;
class VoteController extends BaseAdminController
{
    /**
     * Display a listing of the Vote.
     * @param VoteDataTable \$dataTable
     * @return Response|Factory|RedirectResponse|Redirector|View
     */
    public function index(VoteDataTable \$dataTable)
    {
        return \$dataTable->render(\$this->getViewPath('index'));
    }
}";
        foreach($files as $file){
            $class = QMStr::getShortClassNameFromFilePath($file->getFilename());
            $class = str_replace("Controller", "", $class);
            $contents = str_replace("Vote", $class, $contents);
            FileHelper::writeByFilePath($file->getRealPath(), $contents);
        }
    }
    public function generateApiControllers(): void{
        $files = FileFinder::listFilesRecursively('app/Http/Controllers/API');
        $vote = "<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\BaseAPIController;
/** Class VoteController
 * @package App\Http\Controllers\API
 */
class VoteAPIController extends BaseAPIController {

}";
        foreach($files as $file){
            $class = QMStr::getShortClassNameFromFilePath($file->getFilename());
            $class = str_replace("APIController", "", $class);
            $contents = str_replace("Vote", $class, $vote);
            FileHelper::writeByFilePath($file->getRealPath(), $contents);
        }
    }
    public function generateBladeVariableFallbackSetter(): void{
        $tables = Writable::getTableNames();
        $str = '';
        foreach($tables as $table){
            $str .= " ?? $".QMStr::camelize(QMStr::singularize($table));
        }
    }
    public static function updateButtonNameSpaces(){
        $files = FileFinder::listFiles(QMButton::BUTTONS_FOLDER, true);
        foreach($files as $file){
            $buttonNamespace = "App\Buttons";
            $newNamespace = QMStr::folderToNamespace($file);
            FileHelper::replaceStringInFile($file, 'namespace '.$buttonNamespace.';', 'namespace '.$newNamespace.';');
            $shortClass = QMStr::getShortClassNameFromFilePath($file);
            $oldFullClass = 'App\Buttons\\'.$shortClass;
            $newFullClass = $newNamespace.'\\'.$shortClass;
            FileHelper::replaceStringInAllFilesInFolder('app', $oldFullClass, $newFullClass, 'php');
            FileHelper::replaceStringInAllFilesInFolder('Api', $oldFullClass, $newFullClass, 'php');
            FileHelper::replaceStringInAllFilesInFolder('tests', $oldFullClass, $newFullClass, 'php');
            FileHelper::replaceStringInAllFilesInFolder('Jobs', $oldFullClass, $newFullClass, 'php');
        }
    }
}
