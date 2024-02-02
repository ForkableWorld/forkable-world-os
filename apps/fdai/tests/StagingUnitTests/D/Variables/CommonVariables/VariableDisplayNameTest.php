<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\D\Variables\CommonVariables;
use App\Models\Variable;
use App\Properties\Variable\VariableNameProperty;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use Tests\SlimStagingTestCase;
class VariableDisplayNameTest extends SlimStagingTestCase {
    public function testForeignVariableDisplayName(){
        $v = Variable::findByName("Мама позвонила");
        $this->assertEquals('Мама Позвонила', $v->getTitleAttribute());
    }
    public function testCalciumIntakeDisplayName(){
        $calcium = QMCommonVariable::find(106962);
        $with = VariableNameProperty::addSuffix($calcium->name,
            $calcium->getCommonUnit(),
            true,
            $calcium->getQMVariableCategory());
        $this->assertEquals("Calcium Intake", $with);
    }
    public function testSpinachYesNoVariableDisplayName(){
        $v = QMUserVariable::getByNameOrId(230, "Spinach Raw (yes/no)");
        $this->assertEquals("Spinach Raw", $v->getOrSetVariableDisplayName());
    }
    public function testNumericVariableDisplayName(){
        $dirty = "Unique Test Variable 1603497732797";
        $display = VariableNameProperty::sanitizeSlow($dirty);
        $this->assertEquals($dirty, $display);
    }
}
