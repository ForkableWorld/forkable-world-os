<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Exceptions\NotEnoughMeasurementsForCorrelationException;
use App\Models\UserVariableRelationship;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BaseNumberOfDaysProperty;
use App\VariableRelationships\QMUserVariableRelationship;
class CorrelationNumberOfDaysProperty extends BaseNumberOfDaysProperty
{
    use CorrelationProperty;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    use \App\Traits\PropertyTraits\IsCalculated;
    /**
     * @param QMUserVariableRelationship $model
     * @return float|int
     * @throws NotEnoughMeasurementsForCorrelationException
     * @throws \App\Exceptions\NotEnoughDataException
     * @throws \App\Exceptions\TooSlowToAnalyzeException
     */
    public static function calculate($model){
        $earliestPairStartTime = time();
        $latestPairStartTime = 0;
        $pairs = $model->getPairs();
        foreach($pairs as $pair){
            if($pair->timestamp > $latestPairStartTime){
                $latestPairStartTime = $pair->timestamp;
            }
            if($pair->timestamp < $earliestPairStartTime){
                $earliestPairStartTime = $pair->timestamp;
            }
        }
        $numberOfDays = (int)($latestPairStartTime - $earliestPairStartTime) / 86400;
        static::validateByValue($numberOfDays, $model);
        return $model->numberOfDays = $numberOfDays;
    }
    /**
     * @param $numberOfDays
     * @param QMUserVariableRelationship $model
     * @throws NotEnoughMeasurementsForCorrelationException
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public static function validateByValue($numberOfDays, $model = null){
        if($numberOfDays < BaseNumberOfDaysProperty::MINIMUM_NUMBER_OF_DAYS_IN_COMMON){
            $causeEffect = $model->getCauseAndEffectString();
            throw new NotEnoughMeasurementsForCorrelationException("$causeEffect have less than ".
                BaseNumberOfDaysProperty::MINIMUM_NUMBER_OF_DAYS_IN_COMMON." days of overlapping data in common after pairing. ",
                $model,
                $model->getOrSetCauseQMVariable(),
                $model->getOrSetEffectQMVariable());
        }
    }
    public function showOnIndex(): bool {return true;}
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnDetail(): bool {return true;}
}
