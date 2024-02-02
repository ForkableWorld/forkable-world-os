<?php /** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\SlimTests\Variables;
use App\Buttons\RelationshipButtons\UserVariable\UserVariableBestUserVariableRelationshipButton;
use App\Buttons\RelationshipButtons\UserVariable\UserVariableDefaultUnitButton;
use App\Models\GlobalVariableRelationship;
use App\Models\Measurement;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Models\WpPost;
use App\Properties\Base\BaseVariableIdProperty;
use App\Properties\Measurement\MeasurementStartAtProperty;
use App\Properties\Measurement\MeasurementStartTimeProperty;
use App\Properties\UserVariable\UserVariableStatusProperty;
use App\Slim\Model\Measurement\AnonymousMeasurement;
use App\Storage\DB\TestDB;
use App\Units\OneToFiveRatingUnit;
use App\Utils\Stats;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use Tests\DBUnitTestCase;
/**
 * Class UpdateVariablesTaskTest
 * @package App\Slim\Tasks
 */
class CommonAndUserVariableAnalysisTaskTest extends \Tests\SlimTests\SlimTestCase {
    public function testCommonAndUserVariableAnalysisTask(){
	    TestDB::resetTestDB();
        $this->postTestMeasurements();
        $variableId = BaseVariableIdProperty::fromName('Update Variable User Settings Task Test Variable');
        $userVariable = $this->analyzeAndCheckUserVariable($variableId);
        $dailyValues = [1, 2, 3];
        $this->checkUserVariableRow($dailyValues, $userVariable);
        $this->analyzeAndCheckCommonVariableWithDailyValues($variableId, $dailyValues);
    }
    /**
     * @param int|null $variableId
     * @param array $dailyValues
     */
    private function checkCommonVariableRow(?int $variableId, array $dailyValues): void{
        $updatedCommonVariableRow = Variable::find($variableId);
        $this->assertNotNull($updatedCommonVariableRow->charts);
        $this->assertEquals(Stats::standardDeviation($dailyValues), $updatedCommonVariableRow->standard_deviation);
        $this->assertEquals(Stats::variance($dailyValues), $updatedCommonVariableRow->variance);
        $this->assertEquals(Stats::mean($dailyValues), $updatedCommonVariableRow->mean);
        $this->assertEquals(Stats::median($dailyValues), $updatedCommonVariableRow->median);
        //$this->assertEquals(10, $updatedCommonVariableRow->most_common_original_unit_id);
        $this->assertEquals(3, $updatedCommonVariableRow->most_common_value);
        $this->assertEquals(3, $updatedCommonVariableRow->number_of_unique_values);
        $this->assertEquals(0, round($updatedCommonVariableRow->skewness));
        $this->assertEquals(1, round($updatedCommonVariableRow->kurtosis));
        $this->assertEquals(3, $updatedCommonVariableRow->number_of_measurements,
            "Last 2 measurements should be combined when saving because they're less than 60 seconds apart");
        $key = Variable::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN;
        $this->assertEquals(3, $updatedCommonVariableRow->$key,
            "Last 2 measurements should be combined when saving because they're less than 60 seconds apart");
        $this->assertEquals(1, $updatedCommonVariableRow->number_of_user_variables);
        $this->assertEquals(UserVariableStatusProperty::STATUS_UPDATED, $updatedCommonVariableRow->status);
    }
    /**
     * @param array $dailyValues
     * @param QMUserVariable|null $uv
     */
    private function checkUserVariableRow(array $dailyValues, ?QMUserVariable $uv): void{
        $row = UserVariable::find($uv->id);
        //$this->assertNotNull($row->charts, "no charts!");
        $this->assertNotNull($row->analysis_ended_at);
        $this->assertEquals(Stats::standardDeviation($dailyValues),
            $row->standard_deviation,
            "stdDev not stored in DB");
        $this->assertEquals(Stats::standardDeviation($dailyValues), $uv->standardDeviation);
        $this->assertEquals(Stats::variance($dailyValues), $uv->variance);
        $this->assertEquals(1, $uv->minimumRecordedValue);
        $this->assertEquals(3, $uv->maximumRecordedValue);
        $this->assertEquals(Stats::mean($dailyValues), $uv->mean);
        $this->assertEquals(Stats::median($dailyValues), $uv->median);
        $this->assertNotNull($uv->mostCommonValue,
            "mostCommonValue could vary since we have three different numbers");
        $this->assertEquals(3, $uv->numberOfUniqueDailyValues);
        $this->assertEquals(2, $uv->numberOfChanges);
        $this->assertEquals(0, round($uv->skewness));
        $this->assertEquals(1, round($uv->kurtosis));
        $this->assertEquals(3, $uv->numberOfMeasurements,
            "Last 2 measurements should be combined when saving because they're less than 60 seconds apart");
        $this->assertEquals(3, $uv->numberOfProcessedDailyMeasurements);
        $this->assertEquals(UserVariableStatusProperty::STATUS_UPDATED, $uv->status);
        $this->assertEquals(3, $uv->lastValue);
        $this->assertEquals(2, $uv->secondToLastValue);
        $this->assertEquals(1, $uv->thirdToLastValue);
    }
    private function postTestMeasurements(): void{
        MeasurementStartTimeProperty::setValidationDisabledFor([
            MeasurementStartTimeProperty::class,
            MeasurementStartAtProperty::class,
        ]);
        $this->setAuthenticatedUser(1);
        $postData = '[{"measurements":[
            {"timestamp":1408106260,"value":"1"},
            {"timestamp":1408192660,"value":"2"},
            {"timestamp":1408279060,"value":"3"},
            {"timestamp":1408279061,"value":"3"}],
        "name":"Update Variable User Settings Task Test Variable",
        "source":"Update Variable User Settings Task Test Source",
        "category":"Physical Activity",
        "combinationOperation":"MEAN",
        "unit":"/5"}]';
        $response = $this->postAndGetDecodedBody('/api/v1/measurements', $postData);
        $uv = QMUserVariable::find($response->data->userVariables[0]->userVariableId);
        $measurements = $uv->getQMMeasurements();
        $this->assertCount(3, $measurements);
    }
    /**
     * @param int|null $variableId
     * @return QMUserVariable|null
     */
    public function analyzeAndCheckUserVariable(?int $variableId): ?QMUserVariable{
        DBUnitTestCase::setUserVariablesWithZeroStatusToWaiting();
        QMUserVariable::analyzeWaitingStaleStuck();
        $uv = QMUserVariable::getByNameOrId(1, $variableId);
        $this->assertEquals(3, $uv->lastValue);
        $this->assertEquals(3, $uv->getLastValueInCommonUnit());
        $posts = WpPost::wherePostName($uv->getUniqueIndexIdsSlug())->get();
        $this->assertCount(0, $posts->toArray(),
            "We should not automatically publish user posts due to storage limitations");
        $l = $uv->l();
        $c = $l->best_user_variable_relationship_id;
        if($c){
            $this->assertNotNull($c, $l->print());
            $b = $l->getRelationshipButton(UserVariableBestUserVariableRelationshipButton::class);
            $this->assertEquals("Best User Variable Relationship", $b->title);
        }
        $b = $l->getRelationshipButton(UserVariableDefaultUnitButton::class);
        $this->assertEquals(OneToFiveRatingUnit::NAME, $b->title);
        $this->assertEquals("Unit", $b->subtitle);
        return $uv;
    }
    /**
     * @param int|null $variableId
     * @param array $dailyValues
     */
    public function analyzeAndCheckCommonVariableWithDailyValues(?int $variableId, array $dailyValues): void{
        $commonVariable = QMCommonVariable::find($variableId);
        $commonVariable->analyzeFullyIfNecessary(__FUNCTION__);
        $userVariable = QMUserVariable::getByNameOrId(1, $variableId);
        $this->assertNotNull($userVariable);
        $this->checkCommonVariableRow($variableId, $dailyValues);
        $latest = $commonVariable->getLatestTaggedMeasurementAt();
        $measurements = $commonVariable->getMeasurementsWithTags();
        $last = AnonymousMeasurement::last($measurements);
        $this->assertDateEquals($last->startTime, $latest, "startTime", "latest");
        $this->assertNotNull($commonVariable);
        //$commonVariable->update();
        $this->assertEquals($commonVariable->updatedAt, $commonVariable->analysisEndedAt);
        $posts = WpPost::wherePostName($commonVariable->getUniqueIndexIdsSlug())->get();
        $this->assertCount(0, $posts->toArray());
    }
}
