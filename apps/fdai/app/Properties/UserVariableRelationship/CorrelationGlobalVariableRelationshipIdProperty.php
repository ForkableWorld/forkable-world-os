<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Logging\QMLog;
use App\Models\GlobalVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Storage\DB\Writable;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BaseGlobalVariableRelationshipIdProperty;
use App\Traits\PropertyTraits\IsCalculated;
use App\VariableRelationships\QMUserVariableRelationship;
class CorrelationGlobalVariableRelationshipIdProperty extends BaseGlobalVariableRelationshipIdProperty
{
    use CorrelationProperty;
    use IsCalculated;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    /**
     * @param QMUserVariableRelationship|UserVariableRelationship $model
     * @return mixed|void
     */
    public static function calculate($model){
        $ac = GlobalVariableRelationship::whereCauseVariableId($model->getCauseVariableId())
            ->where(GlobalVariableRelationship::FIELD_EFFECT_VARIABLE_ID, $model->getEffectVariableId())
            ->first();
        if($ac){
            $model->setAttribute(static::NAME, $ac->id);
        }
        return $ac->id;
    }
    public static function updateAll(){
        $updated = Writable::statementStatic("
            update user_variable_relationships c
                join global_variable_relationships ac
                    on ac.cause_variable_id = c.cause_variable_id and
                       ac.effect_variable_id = c.effect_variable_id
                set c.global_variable_relationship_id = ac.id
        ");
        QMLog::info("Updated global_variable_relationship_id for all user_variable_relationships!");
    }
}
