<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\Variable;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseBestGlobalVariableRelationshipIdProperty;
use App\Variables\QMVariable;
class VariableBestGlobalVariableRelationshipIdProperty extends BaseBestGlobalVariableRelationshipIdProperty
{
    use VariableProperty;
    use IsCalculated;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    /**
     * @param QMVariable $model
     * @return int
     */
    public static function calculate($model): ?int{
        $ac = $model->setBestGlobalVariableRelationship();
        if(!$ac){return null;}
        $model->setAttribute(static::NAME, $ac->id);
        return $ac->id;
    }
    public function cannotBeChangedToNull(): bool{
        $v = $this->getVariable();
        return $v->getBestCauseVariableId() || $v->getBestEffectVariableId();
    }
    public function validate(): void {
        parent::validate();
        $v = $this->getVariable();
        $val = $this->getDBValue();
        if($val && !$v->getOptimalValueMessage()){
            $this->throwException("There should be an optimal_value_message since not null");
        }
        if(!$val){
            $cv = $v->getDBModel();
            if($num = $cv->getNumberOfGlobalVariableRelationships()){
                $asCause = $cv->calculateNumberOfGlobalVariableRelationshipsAsCause();
                if($asCause && $cv->isPredictor()){
                    $c = $cv->setBestGlobalVariableRelationship();
                    $this->throwException("$this: No BEST GLOBAL VARIABLE RELATIONSHIP even though NumberOfGlobalVariableRelationships is ".
                        $cv->getNumberOfGlobalVariableRelationships()." $c");
                }
                $asEffect = $cv->calculateNumberOfGlobalVariableRelationshipsAsEffect();
                if($asEffect && $cv->isOutcome()){
                    $c = $cv->setBestGlobalVariableRelationship();
                    $this->throwException("$this: No BEST GLOBAL VARIABLE RELATIONSHIP even though NumberOfGlobalVariableRelationships is ".
                        $cv->getNumberOfGlobalVariableRelationships()." $c");
                }
            }
        }

    }
}
