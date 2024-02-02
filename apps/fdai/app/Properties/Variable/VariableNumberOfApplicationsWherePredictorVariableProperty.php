<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\Application;
use App\Models\Variable;
use App\Traits\PropertyTraits\IsNumberOfRelated;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseNumberOfApplicationsWherePredictorVariableProperty;
class VariableNumberOfApplicationsWherePredictorVariableProperty extends BaseNumberOfApplicationsWherePredictorVariableProperty
{
    use VariableProperty, IsNumberOfRelated;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    public static function getForeignKey():string{
        return self::getRelatedIdField();
    }
    public static function getRelatedIdField(): string{
        return Application::FIELD_PREDICTOR_VARIABLE_ID;
    }
    protected static function getRelationshipClass(): string{
        return Application::class;
    }
}
