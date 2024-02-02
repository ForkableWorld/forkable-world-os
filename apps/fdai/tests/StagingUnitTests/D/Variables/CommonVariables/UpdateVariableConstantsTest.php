<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\D\Variables\CommonVariables;
use App\Slim\Model\QMUnit;
use App\Variables\QMCommonVariable;
use Tests\SlimStagingTestCase;
class UpdateVariableConstantsTest extends SlimStagingTestCase
{
    public function testUpdateDatabaseTableFromHardCodedConstants(){
		$this->skipTest("Don't use hard coded constants");
        QMUnit::updateDatabaseTableFromHardCodedConstants();
        QMCommonVariable::updateDatabaseTableFromHardCodedConstants();
        $this->assertTrue(true);
	}
}
