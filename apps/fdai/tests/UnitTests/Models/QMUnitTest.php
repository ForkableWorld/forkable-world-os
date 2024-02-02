<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Models;
use App\Slim\Model\QMUnit;
use App\Units\MinutesUnit;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use Tests\UnitTestCase;

class QMUnitTest extends UnitTestCase {
	public function testUnitLoad(){
		$unitAbbreviatedName = 'min';
		$unit = QMUnit::findByNameOrSynonym($unitAbbreviatedName);
		$this->assertEquals(MinutesUnit::NAME, $unit->name);
		$unitAbbreviatedName = '/5';
		$expectedResults = [
			'id' => 10,
			'name' => '1 to 5 Rating',
			'abbreviatedName' => '/5',
            'categoryName' => 'Rating',
            'minimumValue' => 1,
            'maximumValue' => 5,
            'conversionSteps' => [
                [
                    'operation' => 'MULTIPLY',
                    'value' => 25,
                ],
                [
                    'operation' => 'ADD',
                    'value' => -25,
                ],
            ],
            'categoryId' => 5,
            'advanced' => 0,
            'unitCategory' => ['name' => 'Rating', 'standardUnitAbbreviatedName' => '%'],
            'manualTracking' => 1
        ];
        $expectedResults = (object) $expectedResults;
        $expectedResults->conversionSteps[0] = (object) $expectedResults->conversionSteps[0];
        $expectedResults->conversionSteps[1] = (object) $expectedResults->conversionSteps[1];
        $unit = QMUnit::findByNameOrSynonym($unitAbbreviatedName);
        $this->assertEquals($expectedResults->name, $unit->name);
    }
    /**
     * @group Model
     * @group Unit
     */
    public function testConvertRatings(){
        for($originalValue = 1; $originalValue < 11; $originalValue++){
            $convertedValue = QMUnit::convertValueByUnitNameOrId($originalValue, '/10',
                '/5', OverallMoodCommonVariable::instance());
            $backConvertedValue = QMUnit::convertValueByUnitNameOrId($convertedValue, '/5',
                '/10', OverallMoodCommonVariable::instance());
            $this->assertEquals(round($originalValue), round($backConvertedValue));
        }
    }
}
