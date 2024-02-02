<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\GlobalVariableRelationship;
use App\Models\BaseModel;
use App\Models\Variable;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseBestEffectVariableIdProperty;
use App\Variables\QMCommonVariable;
class VariableBestEffectVariableIdProperty extends BaseBestEffectVariableIdProperty
{
    use VariableProperty;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    use IsCalculated;
    /**
     * @param Variable $v
     * @return int
     */
    public static function calculate($v){
        $best = GlobalVariableRelationship::whereCauseVariableId($v->getVariableIdAttribute())
            ->where(GlobalVariableRelationship::FIELD_EFFECT_VARIABLE_ID, "<>", $v->getVariableIdAttribute())
            ->orderBy(GlobalVariableRelationship::FIELD_AGGREGATE_QM_SCORE, BaseModel::ORDER_DIRECTION_DESC)
            ->first();
        if(!$best){
            $val = null;
        } else {
            $val = $best->effect_variable_id;
        }
        $v->setAttribute(static::NAME, $val);
        return $val;
    }
    public function validate(): void {
        parent::validate();
        $new = $this->getDBValue();
        $v = $this->getVariable();
        $current = $v->id;
        if($new === $current){
            $this->throwException("must be a different variable");
        }
        if($new && !$v->optimal_value_message){
            $this->throwException("There should be an optimal_value_message since not null");
        }
        $num = $v->number_of_global_variable_relationships_as_cause;
        if($num && !$new){
            $this->throwException("There should be a best effect because there are $num number_of_global_variable_relationships_as_cause");
        }
    }
}
