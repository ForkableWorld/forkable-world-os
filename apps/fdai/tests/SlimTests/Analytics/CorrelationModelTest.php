<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\SlimTests\Analytics;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Models\UserVariableRelationship;
class CorrelationModelTest extends \Tests\SlimTests\SlimTestCase {
	protected function setUp(): void{
		parent::setUp();
		QMUserVariableRelationship::writable()->update([UserVariableRelationship::FIELD_PREDICTS_LOW_EFFECT_CHANGE => 10]);
	}
	public function testGetCorrelationsWithCause(){
		$this->skipTest('TODO: Create global variable relationships test fixture');
		$params['userId'] = 1;
		$params['causeVariableName'] = 'Active Time';
		$correlationsWithCause = QMUserVariableRelationship::getOrCreateUserOrGlobalVariableRelationships($params);
		$this->assertCount(1, $correlationsWithCause);
		foreach($correlationsWithCause as $correlation){
			$this->assertEquals($params['causeVariableName'], $correlation->causeVariableName);
		}
	}
}
