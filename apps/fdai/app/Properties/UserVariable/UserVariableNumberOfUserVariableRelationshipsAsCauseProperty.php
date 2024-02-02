<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariableRelationship;
use App\Models\UserVariable;
use App\Astral\CorrelationBaseAstralResource;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseNumberOfUserVariableRelationshipsAsCauseProperty;
use Illuminate\Http\Request;
use App\Fields\Field;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Variables\QMUserVariable;
class UserVariableNumberOfUserVariableRelationshipsAsCauseProperty extends BaseNumberOfUserVariableRelationshipsAsCauseProperty {
    use UserVariableProperty;
    use IsCalculated;
    const RELATIONSHIP_METHOD = 'best_correlations_where_cause_user_variable';
    const RELATIONSHIP_TITLE = "Outcomes";
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    /**
     * @param UserVariable $model
     * @return int
     */
    public static function calculate($model): int{
        // Use best_correlations_where_cause_user_variable to avoid counting correlation with itself because it conflicts when it's the only one and we can't get a best correlation
        $val = $model->getUserVariable()->best_correlations_where_cause_user_variable()->count();
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return \App\Fields\Field
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
//        return $uv->isPredictor() ?? false;
    }
    public function showOnDetail(): bool {return true;}
}
