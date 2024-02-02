<?php /** @noinspection PhpUnitMissingTargetForTestInspection */
/** @noinspection PhpDeprecationInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\QMUnits;
use App\Slim\Model\QMUnit;
use stdClass;
use Tests\SlimTests\SlimTestCase;

/**
 * @package Tests\UnitTests\QMUnits
 * @coversDefaultClass \App\Slim\Model\QMUnit
 */
class QMUnitsTest extends \Tests\SlimTests\SlimTestCase {
	public function testGet0To5Rating(){
		$unit = QMUnit::getByNameOrId("0 to 5 Rating");
		$this->assertEquals("0 to 5 Rating", $unit->name);
	}

	public function testGetUnits(){
        $this->setAuthenticatedUser(1);
        $response = $this->slimGet('/api/units', []);
        $units = json_decode($response->getBody(), false);
        $this->assertIsArray( $units);
        foreach ($units as $unit) {
            $this->assertInstanceOf('stdClass', $unit);
            $this->checkUnitObjectStructure($unit);
        }
    }
    /**
     * Check that the given object has all required variable properties with correct type
     * @param stdClass $unit
     */
    private function checkUnitObjectStructure($unit){
        $this->assertObjectHasAttribute('maximumValue', $unit);
        $this->assertObjectHasAttribute('minimumValue', $unit);
        $this->assertObjectHasAttribute('name', $unit);
        $this->assertIsString($unit->name);
        $this->assertObjectHasAttribute('abbreviatedName', $unit);
        $this->assertIsString($unit->abbreviatedName);
    }
	public function testGetUnitsForCategoryName(){
        $this->setAuthenticatedUser(1);
        $units = $this->getAndDecodeBody('/api/units', ['categoryName' => 'Duration']);
        foreach ($units as $unit) {
            $this->assertInstanceOf('stdClass', $unit);
            $this->checkUnitObjectStructure($unit);
        }
    }
	/**
	 * @covers \App\Slim\Model\QMUnit::all()
	 */
	public function testGetVariableUnitsForVariableName(){
        $this->setAuthenticatedUser(1);
        $units = $this->getAndDecodeBody('/api/unitsVariable', ['variableName' => 'Overall Mood']);
        $this->assertCount(7, $units);
        /** @var QMUnit[] $units */
        foreach ($units as $unit){
            $this->assertEquals('Rating', $unit->categoryName);
        }
    }
	/**
	 * @codeCoverageIgnore
	 */
	public function testGetUnitsForVariable(){
        $this->setAuthenticatedUser(1);
        $units = $this->getAndDecodeBody('/api/unitsVariable', ['variable' => 'Overall Mood']);
        $this->assertCount(7, $units);
        /** @var QMUnit[] $units */
        foreach ($units as $unit){
            $this->assertEquals('Rating', $unit->categoryName);
        }
    }
}
