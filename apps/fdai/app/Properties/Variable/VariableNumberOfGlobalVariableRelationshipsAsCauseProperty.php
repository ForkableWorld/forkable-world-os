<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\GlobalVariableRelationship;
use App\Models\Variable;
use App\Traits\PropertyTraits\IsNumberOfRelated;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseNumberOfGlobalVariableRelationshipsAsCauseProperty;
use App\Variables\QMCommonVariable;
class VariableNumberOfGlobalVariableRelationshipsAsCauseProperty extends BaseNumberOfGlobalVariableRelationshipsAsCauseProperty
{
    use VariableProperty;
    use IsNumberOfRelated;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    /**
     * @param QMCommonVariable $model
     * @return int
     */
    public static function calculate($model): int{
        $val = GlobalVariableRelationship::whereCauseVariableId($model->getVariableIdAttribute())
            ->count();
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
    public function validate(): void {
        parent::validate();
        $v = $this->getVariable();
        $val = $this->getDBValue();
        if($v->best_effect_variable_id && !$val){
            $this->throwException("best_effect_variable_id but there are not user_variable_relationships");
        }
    }
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {return false;}
    public function showOnDetail(): bool {
        if(!$this->parentIsPopulated()){return true;}
        return $this->getDBValue() || $this->getVariable()->isPredictor() !== false;
        // We can't use logic here /return $this->getVariable()->isPredictor() !== false;
    }
    protected static function getRelatedTable():string{return GlobalVariableRelationship::TABLE;}
    public static function getForeignKey():string{return GlobalVariableRelationship::FIELD_CAUSE_VARIABLE_ID;}
    protected static function getLocalKey():string{return Variable::FIELD_ID;}
    protected static function getRelationshipClass(): string{return GlobalVariableRelationship::class;}

}
