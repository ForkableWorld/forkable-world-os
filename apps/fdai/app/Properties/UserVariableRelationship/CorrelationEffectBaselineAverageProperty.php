<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BaseEffectBaselineAverageProperty;
use App\Traits\PropertyTraits\IsCalculated;
use App\Utils\Stats;
use App\VariableRelationships\QMUserVariableRelationship;
class CorrelationEffectBaselineAverageProperty extends BaseEffectBaselineAverageProperty
{
    use CorrelationProperty;
    use IsCalculated;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    /**
     * @param QMUserVariableRelationship $model
     * @return float|null
     * @throws \App\Exceptions\NotEnoughDataException
     * @throws \App\Exceptions\TooSlowToAnalyzeException
     * @noinspection PhpMissingReturnTypeInspection
     */
    public static function calculate($model) {
        $values = $model->getBaselineEffectValues();
        $val = Stats::average($values, 3);
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
}
