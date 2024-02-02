<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\PhpUnitJobs\Analytics;
use App\Astral\Actions\DeleteTestUsersAction;
use App\Files\Spreadsheet\QMSpreadsheet;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Models\User;
use App\Slim\Model\User\QMUser;
use App\PhpUnitJobs\JobTestCase;
use PhpOffice\PhpSpreadsheet\Exception;
class UserAnalysisJob extends JobTestCase {
	/**
	 * @return void
	 * @throws Exception
	 * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
	 */
	public function testDataQuantity(): void{
		$models = BaseModel::getNonAbstractModelsWithTables();
		$rows[] = ['Model', 'Count', 'Description', 'Category'];
		foreach($models as $model){
			try {
				$rows[] = [$model::getClassTitlePlural(), $model::query()->count(), $model::CLASS_DESCRIPTION,
					$model::CLASS_CATEGORY];
			} catch (\Throwable $e) {
			    QMLog::error(__METHOD__.": ".$e->getMessage());
			}
		}
		QMSpreadsheet::writeToSpreadsheet($rows, 'Model Counts', 'app/PhpUnitJobs/Analytics');
	}
    public function testAnalyzeUsers(){
	    $analyzed = UserAnalysisJob::analyzeUsers();
	    $analyzedInLast24 = User::analysisEndedInLastXHours(24);
	    $this->assertGreaterThan(100, $analyzedInLast24);
        if($analyzedInLast24 < 1000){$this->assertGreaterThan(0, count($analyzed));}
    }
	/**
	 * @return array
	 */
	public static function analyzeUsers(): array{
		self::resetStartTime();
		DeleteTestUsersAction::deleteOldTestUsers();
		$analyzedInLast24 = User::analysisEndedInLastXHours(24);
		$analyzed = QMUser::analyzeWaitingStaleStuck();
		if(!$analyzed){
			$analyzed = QMUser::analyzeStuck();
		}
		if(!$analyzed){
			$analyzed = QMUser::analyzeNeverFinished();
		}
		if(!$analyzed){
			$analyzed = QMUser::analyzeNeverStarted();
		}
		return $analyzed;
	}
}
