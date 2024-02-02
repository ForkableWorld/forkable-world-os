<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\GlobalVariableRelationship;
use App\Logging\QMLog;
use App\Models\GlobalVariableRelationship;
use App\Models\Variable;
use App\Properties\UserVariableRelationship\CorrelationGlobalVariableRelationshipIdProperty;
use App\Properties\UserVariableRelationship\CorrelationIsPublicProperty;
use App\Storage\DB\Writable;
use App\Traits\PropertyTraits\GlobalVariableRelationshipProperty;
use App\Properties\Base\BaseIsPublicProperty;
use App\Traits\PropertyTraits\IsCalculated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
class GlobalVariableRelationshipIsPublicProperty extends BaseIsPublicProperty
{
    use GlobalVariableRelationshipProperty,
        IsCalculated;
    public $table = GlobalVariableRelationship::TABLE;
    public $parentClass = GlobalVariableRelationship::class;
    /**
     * @param GlobalVariableRelationship $model
     * @return bool
     */
    public static function calculate($model): bool{
        $val = $model->hasPublicCorrelation();
        if($model->getCauseVariable()->getIsPublic() && $model->getEffectVariable()->getIsPublic()){
            $val  = true;
        }
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
    public static function updateAll(){
        CorrelationGlobalVariableRelationshipIdProperty::updateAll();
        CorrelationIsPublicProperty::updateAll();
        self::setPublicWhereUserVariableRelationshipIsPublic();
        self::setPrivateWhereOneVariableIsPrivate();
        self::setPublicWhereBothVariablesArePublic();
    }
    protected static function setPublicWhereBothVariablesArePublic(): void{
        $updated = GlobalVariableRelationship::whereHas('cause_variable',
            function($q){
                return $q->where(Variable::FIELD_IS_PUBLIC, 1);
            })->whereHas('effect_variable',
            function($q){
                return $q->where(Variable::FIELD_IS_PUBLIC, 1);
            })->update([GlobalVariableRelationship::FIELD_IS_PUBLIC => true]);
        QMLog::info("Set $updated global variable relationships PUBLIC where the cause and effect were public");
    }
    protected static function setPrivateWhereOneVariableIsPrivate(): void{
        $updated = GlobalVariableRelationship::whereHas('cause_variable',
            function($q){
                return $q->whereNotNull(Variable::FIELD_IS_PUBLIC)
                    ->where(Variable::FIELD_IS_PUBLIC, 0);
            })->update([GlobalVariableRelationship::FIELD_IS_PUBLIC => false]);
        QMLog::info("Set $updated global variable relationships PRIVATE where the cause was private");
        $updated = GlobalVariableRelationship::whereHas('effect_variable',
            function($q){
                return $q->whereNotNull(Variable::FIELD_IS_PUBLIC)
                    ->where(Variable::FIELD_IS_PUBLIC, 0);
            })->update([GlobalVariableRelationship::FIELD_IS_PUBLIC => false]);
        QMLog::info("Set $updated global variable relationships PRIVATE where the effect was private");
    }
    protected static function setPublicWhereUserVariableRelationshipIsPublic(): void{
        Writable::statementStatic("
            update global_variable_relationships ac
            join user_variable_relationships c on ac.id = c.global_variable_relationship_id
            set ac.is_public = true
            where c.is_public = true;
        ");
    }
    /**
     * @param Builder|HasMany $qb
     * @return mixed
     */
    public static function restrict($qb){
        return $qb->where(self::NAME, true)
            ->orderByDesc(GlobalVariableRelationship::FIELD_NUMBER_OF_PAIRS)
            ->where(GlobalVariableRelationship::FIELD_NUMBER_OF_PAIRS, ">",
                GlobalVariableRelationshipNumberOfPairsProperty::MIN_PAIRS_FOR_PUBLIC)
            ->limit(500)
            ;
    }
}
