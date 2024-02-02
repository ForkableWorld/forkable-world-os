<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\GlobalVariableRelationship;
use App\Models\GlobalVariableRelationship;
use App\Models\VariableCategory;
use \App\Traits\PropertyTraits\GlobalVariableRelationshipProperty;
use App\Properties\Base\BaseBoringProperty;
class GlobalVariableRelationshipBoringProperty extends BaseBoringProperty
{
    use GlobalVariableRelationshipProperty;
    public $table = GlobalVariableRelationship::TABLE;
    public $parentClass = GlobalVariableRelationship::class;
    public static function fixNulls(): void {
        self::setNonPredictorCauseCategoriesBoring();
        self::setNonOutcomeEffectCategoriesBoring();
    }
    protected static function setNonPredictorCauseCategoriesBoring(): void{
        foreach(VariableCategory::all() as $cat){
            if(!$cat->predictor && $cat->outcome){
                GlobalVariableRelationship::whereCauseVariableCategoryId($cat->getId())
                    ->whereNull(GlobalVariableRelationship::FIELD_BORING)
                    ->update([GlobalVariableRelationship::FIELD_BORING => true]);
            }
        }
    }
    protected static function setNonOutcomeEffectCategoriesBoring(): void{
        foreach(VariableCategory::all() as $cat){
            if(!$cat->outcome && $cat->predictor){
                GlobalVariableRelationship::whereCauseVariableCategoryId($cat->getId())
                    ->whereNull(GlobalVariableRelationship::FIELD_BORING)
                    ->update([GlobalVariableRelationship::FIELD_BORING => true]);
            }
        }
    }
}
