<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\D\Variables\CommonVariables;
use App\Variables\CommonVariables\SleepCommonVariables\SleepDurationCommonVariable;
use App\Variables\QMCommonVariable;
use Tests\SlimStagingTestCase;
class CommonVariableWordPressPostTest extends SlimStagingTestCase
{
    public function testCommonVariableWordPressPost(){
        $disabled = true;
        if($disabled){
            $this->skipTest("Too slow!");
            return;
        }
		$v = QMCommonVariable::find(SleepDurationCommonVariable::ID);
        $content = $v->generatePostContent();
        $correlations = $v->getCorrelationsAsCause();
        $this->assertGreaterThan(0, count($correlations));
        $correlations = $v->getCorrelationsAsEffect();
        $this->assertGreaterThan(1000, count($correlations));
        $correlations = $v->getGlobalVariableRelationshipsAsCause();
        $this->assertGreaterThan(0, count($correlations));
        $correlations = $v->getGlobalVariableRelationshipsAsEffect();
        $this->assertGreaterThan(0, count($correlations));
        $this->assertNotContains("haven’t found any relationships with", $content);
        $this->assertContains(SleepDurationCommonVariable::NAME, $content);
	}
}
