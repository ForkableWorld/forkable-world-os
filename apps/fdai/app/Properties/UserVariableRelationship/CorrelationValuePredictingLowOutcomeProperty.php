<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\RedundantVariableParameterException;
use App\Models\UserVariableRelationship;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\VariableValueTraits\CauseDailyVariableValueTrait;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BaseValuePredictingLowOutcomeProperty;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Slim\Model\Measurement\Pair;
class CorrelationValuePredictingLowOutcomeProperty extends BaseValuePredictingLowOutcomeProperty
{
    use CorrelationProperty, CauseDailyVariableValueTrait;
    use IsCalculated;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    /**
     * @param float $lowEffectMaximum
     * @param Pair[] $allPairs
     * @param bool $returnExcluded
     * @return Pair[]
     */
    public static function getPairsWithEffectValueBelow(float $lowEffectMaximum, array $allPairs, bool $returnExcluded = false): array{
        $lowEffectPairs = $excluded = [];
        foreach ($allPairs as $iValue) {
            $currentValue = $iValue->effectMeasurementValue;
            if ($currentValue < $lowEffectMaximum) {
                $lowEffectPairs[] = $iValue;
            } else {
                $excluded[] = $iValue;
            }
        }
        if ($returnExcluded) {return $excluded;}
        return $lowEffectPairs;
    }
	/**
	 * @throws InvalidAttributeException
	 * @throws RedundantVariableParameterException
	 */
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
        try {
            $this->validateCauseValue($this->getDBValue(), $this->name, null);
        } catch (InvalidVariableValueException $e) {
            $this->throwException(__METHOD__.": ".$e->getMessage());
        }
    }
    /**
     * @param QMUserVariableRelationship $model
     * @return float
     * @throws InvalidVariableValueException
     * @throws \App\Exceptions\InsufficientVarianceException
     * @throws \App\Exceptions\NotEnoughDataException
     * @throws \App\Exceptions\NotEnoughOverlappingDataException
     * @throws \App\Exceptions\TooSlowToAnalyzeException
     */
    public static function calculate($model): float{
        $lowEffectPairs = CorrelationValuePredictingLowOutcomeProperty::getPairsWithEffectValueBelow(
            $model->getLowEffectCutoffMaximumValue(),
            $model->getPairsBasedOnDailyCauseValues());
        $val = QMUserVariableRelationship::calculateAverageCauseForPairSubset($lowEffectPairs);
        $model->setAvgDailyValuePredictingLowOutcome($val);
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
}
