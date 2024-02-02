<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Astral\CorrelationBaseAstralResource;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseNumberOfUserVariableRelationshipsAsEffectProperty;
use App\Fields\Field;
use App\Variables\QMUserVariable;
class UserVariableNumberOfUserVariableRelationshipsAsEffectProperty extends BaseNumberOfUserVariableRelationshipsAsEffectProperty
{
    use UserVariableProperty;
    use IsCalculated;
    const RELATIONSHIP_METHOD = 'best_correlations_where_effect_user_variable';
    const RELATIONSHIP_TITLE = "Predictors";
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    /**
     * @param UserVariable $model
     * @return int
     */
    public static function calculate($model): int{
        // Use best_correlations_where_cause_user_variable to avoid counting correlation with itself because it conflicts when it's the only one and we can't get a best correlation
	    //$after = $model->getCalculatedAttribute(static::NAME);
	    $model = $model->l();
	    $before = $model->getAttribute(self::NAME);
		//if($after !== null){return $after;} // Avoid duplicate queries
        $qb = $model->getUserVariable()
            ->best_correlations_where_effect_user_variable();
        $val = $qb->count();
        $model->setCalculatedAttribute(static::NAME, $val, $before);
        return $val;
    }
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getDetailsField($resolveCallback = null, string $name = null): Field{
        return CorrelationBaseAstralResource::hasMany(self::RELATIONSHIP_TITLE, self::RELATIONSHIP_METHOD);
    }
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {
        return true;
        // DON'T DO THIS!  It results in irregular columns for different variables!
//        $uv = $this->getUserVariable();
//        if(!$uv->hasId()){return false;}
//        return $uv->isOutcome();
    }
    public function showOnDetail(): bool {return true;}
}
