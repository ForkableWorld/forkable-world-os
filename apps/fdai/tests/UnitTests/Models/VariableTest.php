<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\Models;
use App\Models\Variable;
use App\Variables\CommonVariables\SleepCommonVariables\DurationOfAwakeningsDuringSleepCommonVariable;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Models
 * @coversDefaultClass \App\Models\Variable
 */
class VariableTest extends UnitTestCase {
    public function testGetSeedData(){
        $this->assertNotEmpty(Variable::getSeedData());
    }
    public function testRequiredFields(){
        $this->assertArrayEquals(array (
            0 => 'creator_user_id',
            1 => 'default_unit_id',
            2 => 'synonyms',
        ), (new Variable())->getRequiredFields());
    }
	/**
	 * @return void
	 * @covers \App\Models\Variable::updateDBFromConstants
	 */
	public function testQMCommonVariableUpdateDBFromConstants(){
		$v = Variable::find(DurationOfAwakeningsDuringSleepCommonVariable::ID);
		$qmCV = $v->getQMCommonVariable();
		$this->assertArrayEquals([
			'combination_operation' => 'SUM',
			'default_unit_id' => 2,
			'duration_of_action' => 86400,
			'filling_type' => 'none',
			'id' => 6054544,
			'is_public' => true,
			'manual_tracking' => false,
			'maximum_allowed_value' => 10080,
			'minimum_allowed_seconds_between_measurements' => 86400,
			'minimum_allowed_value' => 0,
			'name' => 'Duration of Awakenings During Sleep',
			'onset_delay' => 0,
			'outcome' => true,
			'synonyms' =>
				[
					0 => 'Duration of Awakenings During Sleep',
				],
			'valence' => 'negative',
			'variable_category_id' => 6,], $qmCV->getHardCodedParametersArray());
		$qmCV->updateDBFromConstants();
		$this->assertEquals(DurationOfAwakeningsDuringSleepCommonVariable::MINIMUM_ALLOWED_VALUE,
			$v->minimum_allowed_value);
	}
}
