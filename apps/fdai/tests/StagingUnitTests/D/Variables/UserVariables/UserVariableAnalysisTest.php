<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\D\Variables\UserVariables;
use App\Variables\QMUserVariable;
use LogicException;
use Tests\SlimStagingTestCase;

class UserVariableAnalysisTest extends SlimStagingTestCase
{
    public function testUserVariableAnalysis(){
        $this->checkBananaFillingValue();
        $this->kneeSwelling();
        $this->berberine();
    }
    private function berberine(): void{
        $berberine = QMUserVariable::findByName(230, "Berberine Plus By Best Naturals");
        if($berberine->lastProcessedDailyValueInCommonUnit === null){
            $berberine->analyzeFully(__FUNCTION__, true);
            if(!$berberine->lastProcessedDailyValueInCommonUnit === null){
                throw new LogicException("no lastProcessedDailyValueInCommonUnit");
            }
        }
    }
    private function kneeSwelling(): void{
        $variable = QMUserVariable::getByNameOrId(230, "Knee Swelling");
        $variable->analyzeFully("testing", true);
        $this->assertTrue(strtotime($variable->getAnalysisEndedAt()) > time() - 3600);
        $this->assertTrue($variable->numberOfProcessedDailyMeasurements > 0.9 * $variable->numberOfMeasurements);
        $this->assertTrue($variable->numberOfMeasurements > 0.9 * $variable->numberOfProcessedDailyMeasurements);
        $this->checkQueryCount(41);
    }
    private function checkBananaFillingValue(): void{
        $banana = QMUserVariable::getByNameOrId(230, "Bananas (grams)");
        $cv = $banana->getVariable();
        $uv = $banana->getDbRow();
        $this->assertEquals(0, $banana->getFillingValueAttribute());
        $this->assertEquals(0, $cv->filling_value);
        $this->assertEquals(0, $uv->fillingValue);
    }
    public function testUserVariableAnalysisUser56571Variable5985026(){
        QMUserVariable::getByNameOrId(56571, 5985026)->analyzeFully(__FUNCTION__);
        $this->checkTestDuration(15);
        $this->checkQueryCount(66);
    }
    public function testUserVariableAnalysisUser71088Variable100339(){
        QMUserVariable::getByNameOrId(71088, 100339)->analyzeFully(__FUNCTION__, true);
        $this->checkTestDuration(19);
        $this->checkQueryCount(94);
    }
    public function testUserVariableAnalysisUser71812Variable5974942(){
        QMUserVariable::getByNameOrId(71812, 5974942)->analyzeFully(__FUNCTION__);
        $this->checkTestDuration(8);
        $this->checkQueryCount(36);
    }
    public function testUserVariableAnalysisUser71812Variable6051336(){
        QMUserVariable::getByNameOrId(71812, 6051336)->analyzeFully(__FUNCTION__);
        $this->checkTestDuration(17);
        $this->checkQueryCount(40);
    }
}
