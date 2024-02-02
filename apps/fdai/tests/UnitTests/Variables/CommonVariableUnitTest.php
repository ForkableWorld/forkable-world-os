<?php
namespace Tests\UnitTests\Variables;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use Tests\UnitTestCase;

class CommonVariableUnitTest extends UnitTestCase {
    public function testGetName() {
        $commonVariable = new OverallMoodCommonVariable();
        $this->assertEquals("Overall Mood", $commonVariable->getNameAttribute());
    }
	/**
	 * @covers \App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable::getS3BucketAndFolderPath
	 */
	public function testCommonVariableS3Path(){
        $v = OverallMoodCommonVariable::instance();
        $this->assertEquals('static.quantimo.do/testing/variables/Overall_Mood', $v->getS3BucketAndFolderPath());
        $this->assertObjectNotHasAttribute('userId', $v);
    }
}
