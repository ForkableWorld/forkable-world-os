<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Exceptions\InsufficientVarianceException;
use App\Exceptions\NotEnoughDataException;
use App\Models\UserVariableRelationship;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BaseCauseChangesProperty;
use App\Traits\PropertyTraits\IsCalculated;
use App\Utils\Stats;
use App\VariableRelationships\QMUserVariableRelationship;
class CorrelationCauseChangesProperty extends BaseCauseChangesProperty
{
    use CorrelationProperty;
    use IsCalculated;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    /**
     * @param QMUserVariableRelationship $model
     * @return int
     * @throws NotEnoughDataException
     * @throws \App\Exceptions\TooSlowToAnalyzeException
     */
    public static function calculate($model): int{
        $causeValues = $model->getCauseValues();
        $val = Stats::countChanges($causeValues);
        self::validateByValue($val, $model);
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
    /**
     * @param int $causeChanges
     * @param QMUserVariableRelationship $model
     * @throws InsufficientVarianceException
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public static function validateByValue($causeChanges, $model = null){
        if($causeChanges < CorrelationCauseChangesProperty::MINIMUM_CHANGES){
            throw new InsufficientVarianceException($model,"The available ".
                $model->getCauseNameWithoutCategoryOrUnit().
                " measurements that overlap with the ".$model->getEffectNameWithoutCategoryOrUnit().
                " data have less than ".CorrelationCauseChangesProperty::MINIMUM_CHANGES." changes. ");
        }
    }
}
