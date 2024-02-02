<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\D\Variables;
use App\Models\Variable;
use App\Properties\Variable\VariableNameProperty;
use App\VariableCategories\SoftwareVariableCategory;
use App\Variables\QMCommonVariable;
use Tests\SlimStagingTestCase;
class ManualTrackingTest extends SlimStagingTestCase{
    public function testManualTracking(){
        $v = QMCommonVariable::findByNameOrId("Interana.sonos.com Usage");
        $cat = $v->getQMVariableCategory();
        $this->assertFalse($cat->manualTracking, $cat->name);
        $laravel = Variable::whereVariableCategoryId(SoftwareVariableCategory::ID)
            ->take(100)
            ->get();
        foreach($laravel as $l){
            $this->assertEquals(SoftwareVariableCategory::ID, $l->variable_category_id);
            $this->assertFalse($l->manual_tracking, $l->name);
            $qm = QMCommonVariable::find($l->id);
            $this->assertFalse($qm->manualTracking, $qm->name);
        }
	}
	public function testRescueTimeVariablesForManualTracking(){
        $variables = Variable::whereNameLike(VariableNameProperty::TIME_SPENT_PREFIX)
            ->where(Variable::FIELD_MANUAL_TRACKING, 1)
            ->get();
        $this->assertCount(0, $variables,
            print_r($variables->pluck('name'),true));
    }
}
