<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Logging\QMLog;
use App\Models\UserVariableRelationship;
use App\Models\Unit;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\VariableValueTraits\CauseDailyVariableValueTrait;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BaseValuePredictingHighOutcomeProperty;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Slim\Model\Measurement\Pair;
class CorrelationValuePredictingHighOutcomeProperty extends BaseValuePredictingHighOutcomeProperty
{
    use CorrelationProperty, CauseDailyVariableValueTrait, IsCalculated;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    public $canBeChangedToNull = false;
    public static function fixInvalidRecords(){
        return parent::fixInvalidRecords();
    }
    public static function fixTooSmall(): array{
        $p = new static();
        $qb = static::whereRaw(UserVariableRelationship::TABLE.'.'.$p->name." < ".Unit::TABLE.'.'.Unit::FIELD_MINIMUM_VALUE)
            ->whereNotNull(UserVariableRelationship::TABLE.'.'.$p->name)
            ->whereNotNull(Unit::TABLE.'.'.Unit::FIELD_MINIMUM_VALUE)
            ->join(Unit::TABLE, UserVariableRelationship::TABLE.'.'.UserVariableRelationship::FIELD_CAUSE_UNIT_ID, "=",
                Unit::TABLE.'.'.Unit::FIELD_ID);
        $ids = $qb->pluck(UserVariableRelationship::TABLE.'.'.UserVariableRelationship::FIELD_ID);
        $count = $ids->count();
        \App\Logging\ConsoleLog::info("$count VariableRelationships with $p->name too small for Unit MINIMUM VALUE");
        $i = 0;
        foreach($ids as $id){
            $i++;
            \App\Logging\ConsoleLog::info("PROGRESS: $i of $count completed...");
            $c = UserVariableRelationship::find($id);
            /** @noinspection PhpUnhandledExceptionInspection */
            $c->getDBModel()->analyzeFully(__METHOD__);
            $cause = $c->getCauseVariable()->getDBModel();
            $c = UserVariableRelationship::find($id);
            /** @noinspection PhpUnhandledExceptionInspection */
            $cause->validateValueForCommonVariableAndUnit($c->value_predicting_high_outcome,
                "duration_of_action", $c->duration_of_action);
        }
    }
    /**
     * @param float $highEffectMinimum
     * @param Pair[] $allPairs
     * @param bool $returnExcluded
     * @return Pair[] array
     */
    public static function getPairsWithEffectValueAbove($highEffectMinimum, $allPairs, bool $returnExcluded = false): array{
        if (!isset($highEffectMinimum)) {
            le("No highEffectMinimum!");
        }
        $highEffectPairs = $excluded = [];
        foreach ($allPairs as $pair) {
            // If we use <=, it's very limiting in the number of user_variable_relationships that get calculated
            $currentValue = $pair->effectMeasurementValue;
            if ($currentValue > $highEffectMinimum) {
                $highEffectPairs[] = $pair;
            } else {
                $excluded[] = $pair;
            }
        }
        if ($returnExcluded) {
            return $excluded;
        }
        return $highEffectPairs;
    }
    /**
     * @throws \App\Exceptions\InvalidAttributeException
     * @throws \App\Exceptions\InvalidVariableValueException
     */
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
        $this->validateCauseValue($this->getDBValue(), $this->name, null);
    }
    /**
     * @param QMUserVariableRelationship $model
     * @return float
     * @throws \App\Exceptions\InsufficientVarianceException
     * @throws \App\Exceptions\InvalidVariableValueException
     * @throws \App\Exceptions\NotEnoughDataException
     * @throws \App\Exceptions\NotEnoughOverlappingDataException
     * @throws \App\Exceptions\TooSlowToAnalyzeException
     */
    public static function calculate($model): float{
        $pairs = CorrelationValuePredictingHighOutcomeProperty::getPairsWithEffectValueAbove(
            $model->getHighEffectCutoffMinimumValue(), $model->getPairsBasedOnDailyCauseValues());
        $val = QMUserVariableRelationship::calculateAverageCauseForPairSubset($pairs);
        $model->setAvgDailyValuePredictingHighOutcome($val);
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
}
