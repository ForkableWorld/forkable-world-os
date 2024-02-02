<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\D\Variables\CommonVariables;
use App\Properties\Variable\VariableNameProperty;
use App\Types\QMArr;
use App\Variables\CommonVariables\EnvironmentCommonVariables\WindSpeedCommonVariable;
use App\Variables\CommonVariables\NutrientsCommonVariables\CalciumRDACommonVariable;
use App\Variables\CommonVariables\PhysicalActivityCommonVariables\WalkOrRunDistanceCommonVariable;
use App\Variables\CommonVariables\VitalSignsCommonVariables\BloodPressureDiastolicBottomNumberCommonVariable;
use App\Variables\QMCommonVariable;
use Tests\SlimStagingTestCase;
class CommonVariableAnalysisTest extends SlimStagingTestCase{
    public const DISABLED_UNTIL = "2021-11-01";
    public $retry = true;
    public function testBloodPressureVariableName(): void{
        //QMCommonVariable::updateDatabaseTableFromHardCodedConstants();
        $v = QMCommonVariable::findByNameOrId(BloodPressureDiastolicBottomNumberCommonVariable::NAME);
        $this->assertEquals(BloodPressureDiastolicBottomNumberCommonVariable::NAME, 
            VariableNameProperty::variableToDisplayName($v));
        $this->assertEquals(BloodPressureDiastolicBottomNumberCommonVariable::NAME, 
            $v->getOrSetVariableDisplayName());
        $this->assertTrue($v->manualTracking);
    }
    public function testCommonVariableAnalysisCalcium(): void{
        $this->analyzeAndCheckCommonVariable(CalciumRDACommonVariable::ID);
        $this->checkTestDuration(22); // Not sure instantiating anonymous measurements is sometimes really slow
        // QueryCount changes all the time based on whether user variables need analysis $this->checkQueryCount(25);
    }
    public function testCommonVariableAnalysisWindSpeed(): void{
        if($disabled = true){
            $this->skipTest("Too slow and we don't have enough memory");
	        return;
        }
        $this->analyzeAndCheckCommonVariable(WindSpeedCommonVariable::NAME);
    }
    public function testCommonVariableAnalysisWalkRun(): void {
        if(time() < strtotime(self::DISABLED_UNTIL)){ // Might be temporarily broken
            /** @noinspection SpellCheckingInspection */
            $this->skipTest('Need to debug LogicException: Walk Or Run Distance (1304) for '.
                'jjppjansen-xs4all-nl (82208): MEASUREMENTS_AT_LAST_ANALYSIS 98 > 68 ');
	        return;
        }
        $fromDb = QMCommonVariable::findByNameOrId(WalkOrRunDistanceCommonVariable::NAME);
		$meas = $fromDb->generateValidDailyMeasurementsWithTags();
		$this->assertInstanceOf(\App\Slim\Model\Measurement\AnonymousMeasurement::class, QMArr::first($meas));
        $const = QMCommonVariable::getVariableConstantsById($fromDb->getId());
        $this->assertEquals($const->maximumAllowedValue, $fromDb->maximumAllowedValue);
        $fromDb->analyzeFullyIfNecessary(__FUNCTION__);
    }
}
