<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests;
use App\Buttons\RelationshipButtons\Measurement\MeasurementConnectorButton;
use App\Buttons\RelationshipButtons\Measurement\MeasurementUnitButton;
use App\Models\GlobalVariableRelationship;
use App\Models\BaseModel;
use App\Models\UserVariableRelationship;
use App\Models\Measurement;
use App\Properties\User\UserIdProperty;
use App\Storage\DB\TestDB;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use Tests\UnitTestCase;
class InterestingRelationshipsMenuTest extends UnitTestCase
{
    public const DISABLED_UNTIL = "2023-04-01";
    public function testCorrelationRelationshipsMenu(){
        TestDB::loadCompletedAnalysesFixtures();
        /** @var UserVariableRelationship $c */
        $c = UserVariableRelationship::firstOrFakeSave();
        $c->save();
        $this->assertNotNull($c->getCauseUserVariable());
        $this->assertNotNull($c->getEffectUserVariable());
        $ac = GlobalVariableRelationship::firstOrFakeSave();
        $c->getOrCreateQMGlobalVariableRelationship();
        $this->assertEquals('Bupropion Sr', $ac->getCauseVariableName());
        $this->assertEquals('Overall Mood', $ac->getEffectVariableName());
        $this->assertRelationshipButtonTitles($c, [
            0 => 'Bupropion Sr',
            1 => 'Overall Mood',
            2 => 'Global Variable Relationship',
        ]);
        $this->assertRelationshipButtonSubTitles($c, [
            0 => 'Cause User Variable',
            1 => 'Effect User Variable',
            2 => 'Global Variable Relationship',
        ]);
        $this->assertRelationshipButtonIcons($c, [
            0 => 'fas fa-briefcase-medical',
            1 => 'far fa-grin-tongue-wink',
            2 => 'fas fa-vials',
        ]);
    }
    public function testUserVariableRelationshipsMenu(){
        TestDB::deleteUserData();
        $uv = OverallMoodCommonVariable::instance()
            ->getOrCreateUserVariable(UserIdProperty::USER_ID_TEST_USER)
            ->l();
        $this->assertRelationshipButtonTitles($uv, [
            0 => 'Measurements',
            1 => 'Tracking Reminders',
            2 => 'Overall Mood',
            3 => 'Predictors',
            4 => 'Owner',
            5 => 'Emotions',
        ]);
        $this->assertRelationshipButtonSubTitles($uv, [
            0 => NULL,
            1 => NULL,
            2 => 'Aggregated Variable',
            3 => NULL,
            4 => NULL,
            5 => NULL,
        ]);
    }
    /**
     * @param BaseModel|string $model
     * @param array $expected
     */
    protected function assertRelationshipButtonTitles(BaseModel $model, array $expected){
        $menu = $model->getInterestingRelationshipsMenu();
        $buttons = $menu->getButtons();
        $this->compareAttributeOfObjectsInArray($expected, $buttons, 'title');
    }
    /**
     * @param BaseModel|string $model
     * @param array $expected
     */
    protected function assertRelationshipButtonSubTitles(BaseModel $model, array $expected){
        $menu = $model->getInterestingRelationshipsMenu();
        $buttons = $menu->getButtons();
        $this->compareAttributeOfObjectsInArray($expected, $buttons, 'subtitle');
    }
    /**
     * @param BaseModel|string $model
     * @param array $expected
     */
    protected function assertRelationshipButtonIcons(BaseModel $model, array $expected){
        $menu = $model->getInterestingRelationshipsMenu();
        $buttons = $menu->getButtons();
        $this->compareAttributeOfObjectsInArray($expected, $buttons, 'fontAwesome');
    }
    /**
     * @param BaseModel|string $model
     * @param array $expected
     */
    protected function assertRelationshipButtonTooltips(BaseModel $model, array $expected){
        $menu = $model->getInterestingRelationshipsMenu();
        $buttons = $menu->getButtons();
        $this->compareAttributeOfObjectsInArray($expected, $buttons, 'tooltip');
    }
    public function testMeasurementRelationshipsMenu(){
        $model = Measurement::fakeFromPropertyModels();
	    $unit = new MeasurementUnitButton($this, $model->unit());
		$this->assertEquals('1 to 5 Rating', $unit->getTitleAttribute());
	    $connector = new MeasurementConnectorButton($this, $model->connector());
	    $this->assertEquals('Fitbit', $connector->getTitleAttribute());
	    $this->compareAttributeOfObjectsInArray([
		    0 => 'Emotions',
		    1 => '1 to 5 Rating',
		    2 => 'Overall Mood',
		    3 => 'Fitbit',
	    ], $model->getInterestingRelationshipButtons(), 'title');
        $this->assertRelationshipButtonTitles($model, [
            0 => 'Emotions',
            1 => '1 to 5 Rating',
            2 => 'Overall Mood',
            3 => 'Fitbit',
        ]);
        $this->assertRelationshipButtonSubTitles($model, [
            0 => 'Variable Category',
            1 => 'Unit',
            2 => 'User Variable',
            3 => 'Data Source',
        ]);
    }
	/**
	 * @return void
	 * @covers \App\Models\Variable::getInterestingRelationshipButtons
	 */
	public function testVariableRelationshipsMenu(){
        TestDB::loadCompletedAnalysesFixtures();
        $v = OverallMoodCommonVariable::instance()->l();
        $this->assertRelationshipButtonTooltips($v, array (
            0 => '1 to 5 Rating is the Default Unit for this Variable',
            1 => 'Emotions is the Variable Category for this Variable',
            2 => 'Analyses of factors that could influence Overall Mood for the average person based on aggregated population level data. ',
            3 => 'Analyses of possible effects of Overall Mood for the average person based on aggregated population level data. ',
            4 => 'Variable statistics, analysis settings, and overviews with data visualizations and likely outcomes or predictors based on data for a specific individual',
            5 => 'Measurements are any value that can be recorded like daily steps, a mood rating, or apples eaten. ',
        ));
        $this->assertRelationshipButtonTitles($v, [
            0 => '1 to 5 Rating',
            1 => 'Emotions',
            2 => 'Factors',
            3 => 'Outcomes',
            4 => 'User Variables',
            5 => 'Measurements',
        ]);
        $this->assertRelationshipButtonSubTitles($v, [
            0 => 'Default Unit',
            1 => 'Variable Category',
            2 => NULL,
            3 => NULL,
            4 => NULL,
            5 => NULL,
        ]);
    }
    private function compareArrays(array $expected, array $actual){
        $expected = $this->exportArrayToString($expected);
        $actual = $this->exportArrayToString($actual);
        $this->assertEquals($expected, $actual, "Replace
$expected
with:
$actual
if it looks good");
    }
    /**
     * @param array $arr
     * @return array|string|string[]
     */
    private function exportArrayToString(array $arr): string {
        $str = var_export($arr, true);
        $str = str_replace('\n', '', $str);
        return $str;
    }
    /**
     * @param array $expected
     * @param $buttons
     * @param string $attribute
     */
    protected function compareAttributeOfObjectsInArray(array $expected, $buttons, string $attribute): void{
        $actual = collect($buttons)->pluck($attribute)->all();
        $this->compareArrays($expected, $actual);
    }
}
