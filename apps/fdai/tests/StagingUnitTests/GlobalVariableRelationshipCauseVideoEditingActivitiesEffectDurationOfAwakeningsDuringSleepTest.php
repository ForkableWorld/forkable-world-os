<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests;
use App\Models\Variable;
use App\Variables\CommonVariables\SleepCommonVariables\DurationOfAwakeningsDuringSleepCommonVariable;
use App\Variables\QMCommonVariable;
use Tests\SlimStagingTestCase;
use App\VariableRelationships\QMGlobalVariableRelationship;
class GlobalVariableRelationshipCauseVideoEditingActivitiesEffectDurationOfAwakeningsDuringSleepTest extends SlimStagingTestCase
{
    public function testGlobalVariableRelationshipCauseVideoEditingActivitiesEffectDurationOfAwakeningsDuringSleep(): void{
		//QMCommonVariable::updateDatabaseTableFromHardCodedConstants();
		$v = Variable::find(DurationOfAwakeningsDuringSleepCommonVariable::ID);
		//$v->updateDBFromConstants();
		$this->assertEquals(DurationOfAwakeningsDuringSleepCommonVariable::MINIMUM_ALLOWED_VALUE, $v->minimum_allowed_value);
		$c = QMGlobalVariableRelationship::getOrCreateByIds(5956927 ,6054544);
		$c->analyzeFully('we are testing');
		$this->checkTestDuration(10);
		$this->checkQueryCount(18);
	}
}
