<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\GlobalVariableRelationship;
use App\VariableRelationships\QMGlobalVariableRelationship;
use App\Logging\QMLog;
use App\Models\GlobalVariableRelationship;
use App\Properties\Base\BaseGroupedCauseValueClosestToValuePredictingHighOutcomeProperty;
use App\Traits\PropertyTraits\GlobalVariableRelationshipProperty;
use App\Traits\PropertyTraits\IsAverageOfCorrelations;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\VariableValueTraits\CauseAggregatedVariableValueTrait;
use Throwable;
class GlobalVariableRelationshipGroupedCauseValueClosestToValuePredictingHighOutcomeProperty extends BaseGroupedCauseValueClosestToValuePredictingHighOutcomeProperty
{
    use GlobalVariableRelationshipProperty, CauseAggregatedVariableValueTrait, IsAverageOfCorrelations;
    public $table = GlobalVariableRelationship::TABLE;
    public $parentClass = GlobalVariableRelationship::class;
    use IsCalculated;
    public static function fixInvalidRecords() {
        QMLog::infoWithoutContext('=== ' . __FUNCTION__ . ' ===');
        $qb = QMGlobalVariableRelationship::readonly()
            ->whereNull(GlobalVariableRelationship::FIELD_DELETED_AT)
            ->whereNull(GlobalVariableRelationship::FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_HIGH_OUTCOME)
            ->whereLike(GlobalVariableRelationship::FIELD_DATA_SOURCE_NAME, "%user%");
        $rows = $qb->getArray();
        QMLog::info(count($rows) . " before");
        foreach ($rows as $row) {
            try {
                $c = QMGlobalVariableRelationship::getByNamesOrIds($row->cause_variable_id, $row->effect_variable_id);
                $c->logInfo("");
            } catch (Throwable $exception) {
                QMLog::error($exception->getMessage());
            }
        }
        foreach ($rows as $row) {
            try {
                $c = QMGlobalVariableRelationship::getByNamesOrIds($row->cause_variable_id, $row->effect_variable_id);
                $c->analyzeFullyAndSave(__FUNCTION__);
            } catch (Throwable $exception) {
                QMLog::error($exception->getMessage());
            }
        }
        $rows = $qb->getDBModels();
        QMLog::info(count($rows) . " after");
    }
}
