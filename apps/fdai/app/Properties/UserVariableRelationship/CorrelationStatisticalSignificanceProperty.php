<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BaseStatisticalSignificanceProperty;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Models\Vote;
use App\Traits\PropertyTraits\IsCalculated;
use App\Variables\QMUserVariable;
class CorrelationStatisticalSignificanceProperty extends BaseStatisticalSignificanceProperty
{
    use CorrelationProperty;
    use IsCalculated;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    public $canBeChangedToNull = false;
    public $required = true;
    public function validate(): void {
        parent::validate();
    }
    /**
     * @param QMUserVariableRelationship $model
     * @return float
     */
    public static function calculate($model){
        if($model->voteStatisticalSignificance === null){
            $model->voteStatisticalSignificance =
                self::calculateVoteSignificance($model->getOrSetCauseQMVariable(), $model->getOrSetCauseQMVariable());
        }
        $numberOfDataPointsForSignificance = 30;
        $numberOfCauseChangesForSignificance = 30;
        $model->rawCauseMeasurementSignificance = 1 - exp(-$model->causeNumberOfRawMeasurements / $numberOfDataPointsForSignificance);
        $model->rawEffectMeasurementSignificance = 1 - exp(-$model->effectNumberOfRawMeasurements / $numberOfDataPointsForSignificance);
        $model->numberOfDaysSignificance = 1 - exp(-$model->numberOfDays / $numberOfDataPointsForSignificance);
        $model->causeChangesStatisticalSignificance = 1 - exp(-$model->causeChanges / $numberOfCauseChangesForSignificance);
        $model->allPairsSignificance = 1 - exp(-$model->numberOfPairs / $numberOfDataPointsForSignificance);
        $val = $model->causeChangesStatisticalSignificance *
            $model->voteStatisticalSignificance *
            $model->rawCauseMeasurementSignificance *
            $model->rawEffectMeasurementSignificance *
            $model->numberOfDaysSignificance *
            $model->allPairsSignificance;
        if($val < 0.001){
            // The current mysql column format converts anything less to 0.
            // We have to divide by this when calculating global variable relationships
            // So we set to 0.001 to avoid division by 0
            $val = 0.001;
        }
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
    /**
     * @param QMUserVariable $causeVariable
     * @param QMUserVariable $effectVariable
     * @return float|int
     */
    public static function calculateVoteSignificance(QMUserVariable $causeVariable, QMUserVariable $effectVariable){
        $downVotes = count(Vote::getDownVotes($causeVariable->getVariableIdAttribute(),
            $effectVariable->getVariableIdAttribute()));
        $upVotes = count(Vote::getUpVotes($causeVariable->getVariableIdAttribute(),
            $effectVariable->getVariableIdAttribute()));
        return ($upVotes + 4) / ($upVotes + $downVotes + 4);
    }
}
