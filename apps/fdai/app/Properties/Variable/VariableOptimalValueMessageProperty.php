<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\Variable;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseOptimalValueMessageProperty;
use App\Variables\QMVariable;
class VariableOptimalValueMessageProperty extends BaseOptimalValueMessageProperty
{
    use VariableProperty;
    use IsCalculated;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    /**
     * @param QMVariable $model
     * @return string
     */
    public static function calculate($model): ?string{
        $msg = static::generate($model);
        if($msg){$model->setAttribute(static::NAME, $msg);}
        return $msg;
    }
    /**
     * @param QMVariable $model
     * @return string
     */
    public static function generate($model): ?string{
        $c = $model->getBestGlobalVariableRelationship();
        $model->setBestStudyLink();
        if(!$c){return null;}
        return $c->generatePredictorExplanationSentence().
            $c->changeFromBaselineSentence();
    }
    public function validate(): void {
        parent::validate();
    }
    public function getShouldNotContain(): array{
        return [
            'This individual',
        ];
    }
    public function cannotBeChangedToNull(): bool{
        $v = $this->getVariable();
        return $v->best_cause_variable_id || $v->best_effect_variable_id || $v->best_global_variable_relationship_id;
    }
}
