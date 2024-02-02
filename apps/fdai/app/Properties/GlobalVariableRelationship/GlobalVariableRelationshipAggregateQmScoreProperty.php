<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\GlobalVariableRelationship;
use App\Models\GlobalVariableRelationship;
use App\Storage\DB\QMDB;
use App\Storage\DB\Writable;
use App\Traits\PropertyTraits\GlobalVariableRelationshipProperty;
use App\Models\BaseModel;
use App\Properties\Base\BaseAggregateQmScoreProperty;
use App\VariableRelationships\QMGlobalVariableRelationship;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Traits\PropertyTraits\IsCalculated;
class GlobalVariableRelationshipAggregateQmScoreProperty extends BaseAggregateQmScoreProperty
{
    use GlobalVariableRelationshipProperty;
    use IsCalculated;
    public $table = GlobalVariableRelationship::TABLE;
    public $parentClass = GlobalVariableRelationship::class;
    /**
     * @param GlobalVariableRelationship $c
     * @return float
     */
    public static function calculate($c){
        $coeff = GlobalVariableRelationshipForwardPearsonCorrelationCoefficientProperty::calculate($c);
        $value = abs($coeff);
        $value *= $c->calculateWeightedAverageVote();
        $users = GlobalVariableRelationshipNumberOfUsersProperty::calculate($c);
        $value *= (1 - exp(-$users / QMGlobalVariableRelationship::SIGNIFICANT_NUMBER_OF_USERS));
        $pairs = GlobalVariableRelationshipNumberOfPairsProperty::calculate($c);
        $value *= (1 - exp(-$pairs / QMGlobalVariableRelationship::SIGNIFICANT_NUMBER_OF_PAIRS));
        $highEffectChange = GlobalVariableRelationshipPredictsHighEffectChangeProperty::calculate($c);
        if ($highEffectChange !== null) {
            $lowEffectChange = GlobalVariableRelationshipPredictsLowEffectChangeProperty::calculate($c);
            // Filter out user_variable_relationships with very little change
            // We can't use predictsHighEffectChange/predictsLowEffectChange directly because effects with zero value outcomes like Number of Zits produce absurdly high percent changes
            $changeSpread = abs($highEffectChange - $lowEffectChange);
            if (!$changeSpread) {
                $changeSpread = 1;
            } // We don't want QM Score to be 0
            $value *= (1 - exp(-$changeSpread / QMUserVariableRelationship::SIGNIFICANT_CHANGE_SPREAD));
        }
        $c->setAttribute(static::NAME, $value);
        return $value;
    }
    /**
     * @return GlobalVariableRelationship|BaseModel
     */
    public function getGlobalVariableRelationship(): GlobalVariableRelationship {
        return $this->getParentModel();
    }
    public static function updateAll(){
        $sql = "ifnull(ac.average_vote, 0)";
        if(Writable::getConnectionName() === 'pgsql'){
            $sql = "coalesce(ac.average_vote, 0)";
        }
        Writable::statementStatic("
            update global_variable_relationships ac
                join variables c on ac.cause_variable_id = c.id
                join variables e on ac.effect_variable_id = e.id
            set ac.aggregate_qm_score =
            (1 - exp(-ac.number_of_users / 10)) *
            (abs(ac.forward_pearson_correlation_coefficient) + $sql *
            IF(c.variable_category_id = 1, 0.1, 1) *
            IF(e.variable_category_id in (1, 10), 1, 0.5)
        ");
    }
}
