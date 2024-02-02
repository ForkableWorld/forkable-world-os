<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Cleanup;
use App\Models\Variable;
use App\PhpUnitJobs\JobTestCase;
use App\Properties\Variable\VariableNameProperty;
class DeleteVariableJob extends JobTestCase {
    public function testDeleteCommonVariable() {
        $v = Variable::whereLike(VariableNameProperty::NAME, '%Upwork%')
            ->where(Variable::FIELD_NUMBER_OF_AGGREGATE_CORRELATIONS_AS_CAUSE, ">", 5)
            ->get();
        foreach ($v as $variable) {
            $variable->hardDeleteWithRelations(__FUNCTION__);
        }
        foreach ($v as $variable) {
            $strongest = $variable->getBestGlobalVariableRelationship();
            $cause = $strongest->getCauseVariable()->getNameOrTitle();
            $variable->analyzeFully(__FUNCTION__);
        }
    }
}
