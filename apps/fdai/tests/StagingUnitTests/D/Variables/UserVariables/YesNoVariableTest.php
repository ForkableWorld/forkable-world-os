<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\D\Variables\UserVariables;
use App\Properties\User\UserIdProperty;
use App\Slim\Model\QMUnit;
use App\Units\YesNoUnit;
use App\Variables\QMUserVariable;
use Tests\SlimStagingTestCase;
class YesNoVariableTest extends SlimStagingTestCase
{
    public function testYesNoVariable(){
        if(true){
            $this->skipTest("Figure out how to deal with screwed up units");
            return;
        }
        $v = QMUserVariable::getByNameOrId(UserIdProperty::USER_ID_MIKE, 5964100);
        $name = $v->name;
        $displayName = $v->displayName;
        $v->logInfo($v->displayName);
        $this->assertVariableUnitIs(YesNoUnit::NAME, $v);
        $this->assertEquals("Spinach Raw (yes/no)", $name);
        $this->assertNotContains("(yes/no)", $displayName);
        $this->assertContains(" measurements (yes/no)", $v->subtitle);
        $this->assertEquals(QMUnit::INPUT_TYPE_yesOrNo, $v->inputType);
    }
}
