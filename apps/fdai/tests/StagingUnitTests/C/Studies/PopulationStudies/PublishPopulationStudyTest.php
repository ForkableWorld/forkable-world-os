<?php /** @noinspection PhpUnhandledExceptionInspection */ /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\C\Studies\PopulationStudies;
use App\Properties\Study\StudyTypeProperty;
use App\Studies\QMPopulationStudy;
use Tests\SlimStagingTestCase;

class PublishPopulationStudyTest extends SlimStagingTestCase {
    public function testPublishPopulationStudy(){
        $cause = "5 HTP";
        //$cause = "Melatonin";
        $effect = "Overall Mood";
        $study = QMPopulationStudy::getStudyIfExists($cause, $effect, null, StudyTypeProperty::TYPE_POPULATION);
        //$correlation = GlobalVariableRelationship::getAggregatedCorrelationByNamesOrIds($cause, $effect);
        //$study = $correlation->getStudy();
        $post = $study->postToWordPress();
        $this->assertEquals(230, $post->post_author);
        $this->assertContains(" for Population", $post->post_title);
        $this->assertNotContains(" for System", $post->post_title);
		$this->checkTestDuration(9);
		$this->checkQueryCount(29);
	}
}
