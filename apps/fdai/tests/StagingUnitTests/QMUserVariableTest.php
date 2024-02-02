<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests;
use Tests\SlimStagingTestCase;
use App\Variables\QMUserVariable;
class QMUserVariableTest extends SlimStagingTestCase
{
    public function testQMUserVariable(): void{
		$l = QMUserVariable::find(222365);
		$l->test();
		$this->checkTestDuration(10);
		$this->checkQueryCount(32);
	}
}
