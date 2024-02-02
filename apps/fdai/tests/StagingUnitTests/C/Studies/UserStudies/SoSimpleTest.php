<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\C\Studies\UserStudies;
use App\Models\GlobalVariableRelationship;
use App\Studies\QMStudy;
use App\Variables\CommonVariables\EmotionsCommonVariables\AlertnessCommonVariable;
use App\Variables\CommonVariables\TreatmentsCommonVariables\FlaxOilCommonVariable;
use Tests\SlimStagingTestCase;
class SoSimpleTest extends SlimStagingTestCase {
    public function testUserStudyShowHtml(){
        $this->skipTest("Changes too much");
        $study = QMStudy::find('cause-1482-effect-1444-user-230-user-study');
        $c = $study->getHasCorrelationCoefficient();
        $this->assertEquals($c->getNumberOfPairs(), $c->l()->getNumberOfPairs());
        $this->compareHtmlPage('study', $study->getShowPageHtml());
	}
    public function testPopulationStudyShowHtml(){
        $this->skipTest("Changes too much");
        $study = GlobalVariableRelationship::findByVariableNamesOrIds(FlaxOilCommonVariable::NAME,
            AlertnessCommonVariable::NAME);
        $this->compareHtmlPage('study', $study->getShowPageHtml());
    }
}
