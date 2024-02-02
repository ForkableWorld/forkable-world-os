<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests;
use Tests\SlimStagingTestCase;
use App\Variables\QMCommonVariable;
class CurrentQuarterlyAverageGradeForAlgebraQMCommonVariableTest extends SlimStagingTestCase
{
    public function testCurrentQuarterlyAverageGradeForAlgebraQMCommonVariable(): void{
		$l = QMCommonVariable::find(6060380);
		$l->test();
		$this->checkTestDuration(10);
		$this->checkQueryCount(41);
	}
}
