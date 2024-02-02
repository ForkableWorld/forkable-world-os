<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Models\VariableCategory;
use \App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BaseBoringProperty;
class CorrelationBoringProperty extends BaseBoringProperty
{
    use CorrelationProperty;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    public static function fixNulls(): void {
        self::setNonPredictorCauseCategoriesBoring();
        self::setNonOutcomeEffectCategoriesBoring();
    }
    protected static function setNonPredictorCauseCategoriesBoring(): void{
        foreach(VariableCategory::all() as $cat){
            if(!$cat->predictor && $cat->outcome){
                UserVariableRelationship::whereCauseVariableCategoryId($cat->getId())
                    ->whereNull(UserVariableRelationship::FIELD_BORING)
                    ->update([UserVariableRelationship::FIELD_BORING => true]);
            }
        }
    }
    protected static function setNonOutcomeEffectCategoriesBoring(): void{
        foreach(VariableCategory::all() as $cat){
            if(!$cat->outcome && $cat->predictor){
                UserVariableRelationship::whereCauseVariableCategoryId($cat->getId())
                    ->whereNull(UserVariableRelationship::FIELD_BORING)
                    ->update([UserVariableRelationship::FIELD_BORING => true]);
            }
        }
    }
}
