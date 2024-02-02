<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Models\VariableCategory;
use App\Properties\Variable\VariableIsPublicProperty;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BaseIsPublicProperty;
use App\Traits\PropertyTraits\IsCalculated;
use App\Types\BoolHelper;
class CorrelationIsPublicProperty extends BaseIsPublicProperty
{
    use CorrelationProperty, IsCalculated;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    /**
     * @param UserVariableRelationship $model
     * @return bool
     */
    public static function calculate($model): bool{
        $val = $model->getCauseUserVariable()->getIsPublic() &&
            $model->getEffectUserVariable()->getIsPublic();
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
    public static function updateAll(){
        VariableIsPublicProperty::updateAll();
        $privateCategories = VariableCategory::whereIsPublic(false)->get();
    }
    /**
     * Set the default options for the filter.
     *
     * @return string
     */
    public function defaultFilter(): string{return BoolHelper::ALL_STRING;}
    public function showOnIndex(): bool {return false;}
    public function showOnUpdate(): bool {return true;}
    public function showOnCreate(): bool {return false;}
    public function showOnDetail(): bool {return true;}
}
