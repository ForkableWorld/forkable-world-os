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
use App\Properties\Base\BaseNumberOfApplicationsWhereOutcomeVariableProperty;
class VariableNumberOfApplicationsWhereOutcomeVariableProperty extends BaseNumberOfApplicationsWhereOutcomeVariableProperty
{
    use VariableProperty, IsNumberOfRelated;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    public static function getForeignKey():string{return self::getRelatedIdField();}
    public static function getRelatedIdField(): string{return  Application::FIELD_OUTCOME_VARIABLE_ID;}
    protected static function getRelationshipClass(): string{return Application::class;}
    protected static function getRelatedTable():string{return Application::TABLE;}
    protected static function getLocalKey():string{return Variable::FIELD_ID;}
}
