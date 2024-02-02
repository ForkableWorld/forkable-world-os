<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\D\Variables\CommonVariables;
use App\Variables\QMUserVariable;
use Tests\SlimStagingTestCase;

class VariableStaticPropertiesTest extends SlimStagingTestCase
{
    public function testVariableStaticProperties(){
        //CommonVariable::updateDatabaseTableFromHardCodedConstants();
        $blood = QMUserVariable::findByNameIdSynonymOrSpending(230, "Blood Pressure (Systolic - Top Number)");
        $name = $blood->getOrSetVariableDisplayName();
        $this->assertEquals("Blood Pressure", $name);
	}
}
