<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
namespace Tests\UnitTests\Variables;
use App\Buttons\QMButton;
use App\Models\Variable;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Variable\VariableIdProperty;
use App\Storage\DB\TestDB;
use App\Variables\CommonVariables\PhysiqueCommonVariables\BodyFatCommonVariable;
use App\Variables\CommonVariables\TreatmentsCommonVariables\BupropionSrCommonVariable;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\CommonVariables\PhysicalActivityCommonVariables\WalkOrRunDistanceCommonVariable;
use App\Variables\CommonVariables\VitalSignsCommonVariables\CoreBodyTemperatureCommonVariable;
use App\Variables\QMUserVariable;
use App\Variables\QMCommonVariable;
use Tests\UnitTestCase;
use RuntimeException;
class VariableTest extends UnitTestCase {
    /**
     * @throws RuntimeException
     */
    public function testSetCombinationOperation(){
        // test case-insensitive
        $combinationOperation = 'sum';
        $expectedOperation = 'SUM';
        $variable = new QMCommonVariable();
        $variable->setCombinationOperation($combinationOperation);
        $this->assertEquals($expectedOperation, $variable->combinationOperation);
        $combinationOperation = 0;
        $expectedOperation = 'SUM';
        $variable = new QMCommonVariable();
        $variable->setCombinationOperation($combinationOperation);
        $this->assertEquals($expectedOperation, $variable->combinationOperation);
        $combinationOperation = 'MEAN';
        $expectedOperation = 'MEAN';
        $variable = new QMCommonVariable();
        $variable->setCombinationOperation($combinationOperation);
        $this->assertEquals($expectedOperation, $variable->combinationOperation);
        $combinationOperation = 1;
        $expectedOperation = 'MEAN';
        $variable = new QMCommonVariable();
        $variable->setCombinationOperation($combinationOperation);
        $this->assertEquals($expectedOperation, $variable->combinationOperation);
    }
    /**
     * @group Model
     * @group Variable
     */
    public function testAddMethod(){
        BaseClientIdProperty::setInMemory(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT);
        $newVariableData['parentVariableId'] = null;
        $newVariableData['name'] = 'My Super Mood';
        $newVariableData['variableCategoryId'] = 1;
        $newVariableData['defaultUnitId'] = 40;
        $newVariableData['combinationOperation'] = 'SUM';
        Variable::whereName($newVariableData['name'])->forceDelete(__METHOD__);
        $commonVariableRow = QMCommonVariable::add($newVariableData['name'], $newVariableData);
        $commonVariableRow = (array) $commonVariableRow;
        $variable = Variable::find($commonVariableRow['id'])->toArray();
        $this->assertEquals($variable['id'], $commonVariableRow['id']);
        $this->assertEquals($variable['parent_id'], $newVariableData['parentVariableId']);
        $this->assertEquals($variable['name'], $newVariableData['name']);
        $this->assertEquals($variable['variable_category_id'], $newVariableData['variableCategoryId']);
        $this->assertEquals($variable['default_unit_id'], $newVariableData['defaultUnitId']);
        $this->assertEquals('MEAN', $variable['combination_operation'], 'Cannot use SUM for this unit');
    }
    /**
     * The following variables are public (list of variable names):
     * - 'Back Pain'
     * - 'Body Mass Index Or BMI'
     * - 'Body Fat'
     * These variables are also used for getAllLikeWithManyCorrelations() method
     * @group Model
     * @group Variable
     */
    public function testGetAllPublic(){
        $variables = QMCommonVariable::getAllPublic();
        $this->assertGreaterThan(113, count(QMCommonVariable::getAllPublic()));
        $variableNames = [];
        foreach ($variables as $variable) {
            $variableNames[] = $variable->name;
        }
        $this->assertContains('Overall Mood', $variableNames);
    }
    /**
     * @group Model
     * @group Variable
     */
    public function testGetAllVariablesForUser(){
        TestDB::deleteUserData();
        $this->createMoodMeasurements();
        $userId = 1;
        $this->setAuthenticatedUser($userId);
        $variables = QMUserVariable::getUserVariables($userId);
        $this->assertCount(1, $variables);
        foreach ($variables as $variable) {
            $this->assertEquals($userId, $variable->userId);
        }
    }
    /**
     * @group Model
     * @group Variable
     */
    public function testGetAllVariablesForUserInCategory(){
        TestDB::deleteUserData();
        $this->createMoodMeasurements();
        $this->setAuthenticatedUser(1);
        $variables = QMUserVariable::getUserVariables(1, [
            'category' => 'Emotions'
        ]);
        $this->assertGreaterThan(0, count($variables));
        foreach ($variables as $variable) {
            //$this->assertInstanceOf('App\Slim\Model\Variable', $variable);
            $this->assertEquals('Emotions', $variable->variableCategoryName);
        }
    }
    /**
     * @group Model
     * @group Variable
     */
    public function testGetIdByName(){
        $variableId = VariableIdProperty::fromName('Overall Mood');
        $this->assertEquals(1398, $variableId);
        $variableId = VariableIdProperty::fromName('Bad variable name');
        $this->assertNull($variableId);
    }
    public function testGetByNameOrId(){
        TestDB::deleteUserData();
        $this->createMoodMeasurements();
        $variableName = "Overall Mood";
        $userVariable = QMUserVariable::getByNameOrId(1, $variableName);
        $this->assertEquals($variableName, $userVariable->getVariableName());
        $variableId = 1398;
        $userVariable = QMUserVariable::getByNameOrId(1, $variableId);
        $this->assertEquals($variableId, $userVariable->getVariableIdAttribute());
    }
    public function testMaxMinAllowedValues() {
        $v = CoreBodyTemperatureCommonVariable::getUserVariableByUserId(1);
        $this->assertEquals(CoreBodyTemperatureCommonVariable::MAXIMUM_ALLOWED_VALUE,
            $v->maximumAllowedValueInUserUnit);
        $this->assertEquals(CoreBodyTemperatureCommonVariable::MINIMUM_ALLOWED_VALUE,
            $v->minimumAllowedValueInCommonUnit);
        $invalid = $v->valueInvalidForCommonVariableOrUnit(0, "value");
        $this->assertTrue((bool)$invalid);
        $values = $v->getLastValuesInUserUnit();
        $this->assertTrue(!in_array(0, $values));
    }
    public function testAddSynonymLaravel(){
        $v = Variable::find(1398);
        $v->synonyms = OverallMoodCommonVariable::SYNONYMS;
        $v->save();
        $moodBefore = Variable::find(1398);
        $synonymsBefore = $moodBefore->synonyms;
        $newSynonym = "Laravel Test Synonym";
        $this->assertNotContains($newSynonym, $synonymsBefore);
        $moodBefore->addSynonymsAndSave($newSynonym);
        $moodAfter = Variable::find(1398);
        $synonymsAfter = $moodAfter->synonyms;
        $this->assertCount(count($synonymsBefore)+1, $synonymsAfter);
        $this->assertContains($newSynonym, $synonymsAfter);
    }
    public function testAddSynonymSlim(){
        $v = Variable::find(1398);
        $v->synonyms = OverallMoodCommonVariable::SYNONYMS;
        $v->save();
        $moodBefore = QMCommonVariable::find(1398);
        $synonymsBefore = $moodBefore->synonyms;
        $newSynonym = "DBModel Test Synonym";
        $this->assertNotContains($newSynonym, $synonymsBefore);
        $moodBefore->addSynonym($newSynonym);
		$this->assertArrayEquals( [
			0 => 'Mood',
			1 => 'Overall Mood',
			2 => 'Happy',
			3 => 'Happiness',
			4 => 'DBModel Test Synonym',
		], $moodBefore->synonyms);
        $this->assertCount(count($synonymsBefore)+1, $moodBefore->synonyms);
        $this->assertContains($newSynonym, $moodBefore->synonyms);
        $moodBefore->save();
        $moodAfter = QMCommonVariable::find(1398);
        $synonymsAfter = $moodAfter->synonyms;
        $this->assertCount(count($synonymsBefore)+1, $synonymsAfter);
        $this->assertContains($newSynonym, $synonymsAfter);
    }
    public function testMinimumAllowedSeconds(){
        $v = QMCommonVariable::find(WalkOrRunDistanceCommonVariable::ID);
        $this->assertEquals(WalkOrRunDistanceCommonVariable::MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS,
            $v->getMinimumAllowedSecondsBetweenMeasurements());
        $uv = QMUserVariable::getOrCreateById(1, $v->id);
        $this->assertEquals(WalkOrRunDistanceCommonVariable::MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS,
            $uv->getMinimumAllowedSecondsBetweenMeasurements());
    }
    public function testRelationshipButtons(){
        $v = OverallMoodCommonVariable::instance();
        $buttons = $v->getRelationshipButtons();
        foreach($buttons as $button){
            $this->assertInstanceOf(QMButton::class, $button);
            //$this->assertContains("astral", $button->getUrl());
        }
    }
	public function testGetMinimumAllowedValueAttribute(){
		$v = Variable::find(BodyFatCommonVariable::ID);
		$this->assertEquals(BodyFatCommonVariable::MINIMUM_ALLOWED_VALUE, $v->getMinimumAllowedDailyValue());
		$this->assertEquals(BodyFatCommonVariable::MINIMUM_ALLOWED_VALUE, $v->getMinimumAllowedValueAttribute());
	}
	public function testGetVariableHtml(){
		$v = Variable::find(BupropionSrCommonVariable::ID);
		$outcomeLabel = $v->getOutcomesLabelHtml();
		$this->compareHtmlFragment("BupropionSr Outcomes Label", $outcomeLabel);
		$html = $v->getHtmlPage(true);
		$this->compareHtmlPage("BupropionSrCommonVariable", $html);
	}
}
