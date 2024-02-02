<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Logging\QMLog;
use App\Models\UserVariableRelationship;
use App\Models\Variable;
use App\Properties\Base\BaseCauseVariableCategoryIdProperty;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Variables\QMVariableCategory;
class CorrelationCauseVariableCategoryIdProperty extends BaseCauseVariableCategoryIdProperty
{
    use CorrelationProperty;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    /**
     * @return array
     */
    public static function deleteCorrelationsWithBoringCauseCategories(): array
    {
        $rows = QMUserVariableRelationship::readonly()
            ->select([
                UserVariableRelationship::TABLE . '.' . UserVariableRelationship::FIELD_USER_ID,
                UserVariableRelationship::TABLE . '.' . UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID,
                UserVariableRelationship::TABLE . '.' . UserVariableRelationship::FIELD_EFFECT_VARIABLE_ID,
                UserVariableRelationship::TABLE . '.' . UserVariableRelationship::FIELD_QM_SCORE,
            ])
            ->join(Variable::TABLE . ' as cv',
                'cv.' . Variable::FIELD_ID, '=',
                UserVariableRelationship::TABLE . '.' . UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID)
            ->whereIn('cv.' . Variable::FIELD_VARIABLE_CATEGORY_ID, QMVariableCategory::getBoringVariableCategoryIds())
            //->limit(10)
            ->getDBModels();
        QMLog::info(count($rows) . " rows");
        $correlations = [];
        foreach ($rows as $row) {
            $row->hardDelete(__FUNCTION__);
        }
        return $correlations;
    }

}
