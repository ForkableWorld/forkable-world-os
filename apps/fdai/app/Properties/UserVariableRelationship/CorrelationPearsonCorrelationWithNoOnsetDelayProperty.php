<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BasePearsonCorrelationWithNoOnsetDelayProperty;
use App\VariableRelationships\QMUserVariableRelationship;
class CorrelationPearsonCorrelationWithNoOnsetDelayProperty extends BasePearsonCorrelationWithNoOnsetDelayProperty
{
    use CorrelationProperty;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    use \App\Traits\PropertyTraits\IsCalculated;
    /**
     * @param QMUserVariableRelationship $model
     * @return float|null
     * @throws \App\Exceptions\NotEnoughDataException
     * @throws \App\Exceptions\TooSlowToAnalyzeException
     * @noinspection PhpMissingReturnTypeInspection
     */
    public static function calculate($model) {
        $overDelays = $model->getOverDelays();
        /** @var QMUserVariableRelationship $zero */
        $zero = collect($overDelays)
            ->where('onsetDelay', 0)
            ->where('durationOfAction', CorrelationCorrelationsOverDelaysProperty::DURATION)
            ->first();
        if(!$zero){
            $model->logError("No correlation with zero onset delay!");
            return null;
        }
        $val = $zero->getForwardPearsonCorrelationCoefficient();
        $model->setAttribute(self::NAME, $val);
        return $val;
    }
}
