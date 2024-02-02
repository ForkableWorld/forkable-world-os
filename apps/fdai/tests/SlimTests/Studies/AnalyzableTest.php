<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\SlimTests\Studies;
use App\Models\User;
use App\Models\UserVariable;
use App\Slim\Model\User\QMUser;
use App\Storage\DB\TestDB;
use App\Units\OneToFiveRatingUnit;
use App\Utils\QMProfile;
use App\VariableCategories\EmotionsVariableCategory;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\QMUserVariable;

use Tests\UnitTestCase;
class AnalyzableTest extends UnitTestCase {
    public function testAnalyzableJobs(): void{
        if($profile = false){QMProfile::startLiveProf();}
        TestDB::deleteUserAndAggregateData();
		User::query()->update([
			User::FIELD_ANALYSIS_ENDED_AT => null,
			User::FIELD_ANALYSIS_STARTED_AT => null,
		]);
        $qb = QMUser::whereNeverStartedAnalyzing();
        $qb = QMUser::excludeUnAnalyzableUsers($qb);
        $usersToAnalyze = $qb->get();
		$totalUsers = User::count();
		$this->assertEquals(11, $totalUsers);
		$whereNeverStartedAnalyzing = $qb->getWhereString(true);
		$this->assertEquals('"wp_users"."analysis_started_at" is null 
  and 
"wp_users"."deleted_at" is null 
  and 
"wp_users"."ID" not in (18535, 13, 0, 3, 7, 12000) 
order by 
  "wp_users"."updated_at" asc', $whereNeverStartedAnalyzing, $whereNeverStartedAnalyzing);
		$this->assertEquals(11, User::whereNull(User::FIELD_ANALYSIS_ENDED_AT)->count());
		$names = $usersToAnalyze->pluck('loginName')->toArray();
		$this->assertArrayEquals(array (
			                         0 => 'quantimodo',
			                         1 => 'quint',
			                         2 => 'asdfds',
			                         3 => 'no-user_variable_relationships-user',
			                         4 => 'dr-quantimo-do',
			                         5 => 'mike',
			                         6 => 'demo',
		                         ), $names);
        $this->checkUsersBeforeAnalysis();
        $this->assertWeCanGetCommonVariableByName(OverallMoodCommonVariable::NAME);
        $this->createGlobalVariableRelationships();
        if($profile){QMProfile::endProfile();}
    }
	/**
	 * @param int $userId
	 * @return QMUserVariable
	 */
	protected function getMoodQMUserVariable(int $userId = 1): QMUserVariable {
		$mood = QMUserVariable::findOrCreateByNameOrId($userId, "Overall Mood", [], [
			'variableCategoryName' => EmotionsVariableCategory::NAME,
			'unitName'             => OneToFiveRatingUnit::NAME
		]);
		$cv = $mood->getCommonVariable();
		$cv->l()->assertHasStatusAttribute();
		return $mood;
	}
    public function checkUsersBeforeAnalysis(): void{
        $this->assertEquals(User::count(), User::whereNull(User::FIELD_ANALYSIS_STARTED_AT)->count());
        $qb = QMUser::whereNeverStartedAnalyzing();
        $qb = QMUser::excludeUnAnalyzableUsers($qb);
        $users = $qb->getDBModels();
        $this->assertGreaterThan(2, count($users), $qb->getWhereString());
        $this->assertNotNull($users[0]->loginName, $qb->getPreparedQuery());
    }
	protected function createTreatmentOutcomeMeasurementsFromFixtures(){
		if($dump = TestDB::shouldRegenerateFixtures()){
			$this->createTreatmentOutcomeMeasurements();
			TestDB::generateSeeds(__FUNCTION__);
		} else {
			TestDB::loadFixtures(__FUNCTION__);
			UserVariable::where(UserVariable::FIELD_NUMBER_OF_MEASUREMENTS_WITH_TAGS_AT_LAST_CORRELATION, ">", 1)->update([UserVariable::FIELD_NUMBER_OF_MEASUREMENTS_WITH_TAGS_AT_LAST_CORRELATION => 0]);
		}
		$this->makeSureVariablesNeedCorrelation(2);
	}
	/**
	 * @param int $expected
	 */
	protected function makeSureVariablesNeedCorrelation(int $expected): void{
		/** @var UserVariable[] $variables */
		$variables = UserVariable::all();
		$this->assertCount($expected, $variables);
		foreach($variables as $uv){
			$this->assertGreaterThan(0, $uv->number_of_unique_daily_values);
			$this->assertGreaterThan(0, $uv->number_of_changes);
			// Kind of slow $this->assertGreaterThan(0, $uv->getSpread());
			$this->assertTrue($uv->needToCorrelate());
			$this->assertEquals(0, $uv->number_of_measurements_with_tags_at_last_correlation);
			$correlations = $uv->getUserVariableIdsToCorrelateWith();
			if(!$correlations){
				$dbm = $uv->getQMUserVariable();
				$dbm->debugCorrelationsQB();
			}
			$this->assertCount(1, $correlations);
		}
	}


}
