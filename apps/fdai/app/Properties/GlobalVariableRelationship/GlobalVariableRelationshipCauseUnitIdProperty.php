<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\GlobalVariableRelationship;
use App\Logging\QMLog;
use App\Models\GlobalVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Properties\Unit\UnitNameProperty;
use App\Traits\PropertyTraits\GlobalVariableRelationshipProperty;
use App\Properties\Base\BaseCauseUnitIdProperty;
use App\Types\QMArr;
use App\VariableRelationships\QMGlobalVariableRelationship;
class GlobalVariableRelationshipCauseUnitIdProperty extends BaseCauseUnitIdProperty
{
    use GlobalVariableRelationshipProperty;
    public $table = GlobalVariableRelationship::TABLE;
    public $parentClass = GlobalVariableRelationship::class;
    public $isCalculated = true;
    /**
     * @param QMGlobalVariableRelationship|GlobalVariableRelationship $aggregateCorrelation
     * @return int
     */
    public static function calculate($aggregateCorrelation): ?int {
        $values = $aggregateCorrelation->pluckFromCorrelations(UserVariableRelationship::FIELD_CAUSE_UNIT_ID);
        if(!$values){
            QMLog::info("TODO: Fix correlation cause units");
            return null;
        }
        $unique = array_unique($values);
        if(count($unique) !== 1){
            $recalculated = $aggregateCorrelation->recalculateUserVariableRelationshipsWithWrongCauseUnitId();
            $newValues = $aggregateCorrelation->pluckFromCorrelations(UserVariableRelationship::FIELD_CAUSE_UNIT_ID);
            $uniqueAfter = array_unique($newValues);
            if(count($uniqueAfter) !== 1){
                le("Different cause units for $aggregateCorrelation even after recalculation: ".
                   \App\Logging\QMLog::print_r(UnitNameProperty::fromIds($uniqueAfter), true).$aggregateCorrelation->getPHPUnitTestUrl());
            }
        }
        $unitId = QMArr::first($values);
        $aggregateCorrelation->setAttribute(static::NAME, $unitId);
        return $unitId;
    }
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {return false;}
    public function showOnDetail(): bool {return false;}
}
