<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\GlobalVariableRelationship;
use App\Logging\QMLog;
use App\Models\GlobalVariableRelationship;
use App\Traits\PropertyTraits\GlobalVariableRelationshipProperty;
use App\Properties\Base\BasePopulationTraitPearsonCorrelationCoefficientProperty;
use App\Utils\Stats;
use Illuminate\Support\Arr;
use App\VariableRelationships\QMGlobalVariableRelationship;
class GlobalVariableRelationshipPopulationTraitPearsonCorrelationCoefficientProperty extends BasePopulationTraitPearsonCorrelationCoefficientProperty
{
    use GlobalVariableRelationshipProperty;
    public $table = GlobalVariableRelationship::TABLE;
    public $parentClass = GlobalVariableRelationship::class;
    use \App\Traits\PropertyTraits\IsCalculated;
    /**
     * @param QMGlobalVariableRelationship $model
     * @return float
     */
    public static function calculate($model): ?float{
        $pairsOfAveragesForAllUsers = $model->getPairsOfAveragesForAllUsers();
        $averageEffectValuesForAllUsers = Arr::pluck($pairsOfAveragesForAllUsers, 'effectVariableAverageValue');
        $averageCauseValuesForAllUsers = Arr::pluck($pairsOfAveragesForAllUsers, 'causeVariableAverageValue');
        if(count($averageCauseValuesForAllUsers) < 2 || count($averageEffectValuesForAllUsers) < 2){
            QMLog::debug("there are not enough average values to calculatePopulationTraitCorrelationPearsonCorrelationCoefficient");
            return null;
        }
        $val = Stats::calculatePearsonCorrelationCoefficient($averageEffectValuesForAllUsers,
            $averageCauseValuesForAllUsers);
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
}
