<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\GlobalVariableRelationship;
use App\Models\GlobalVariableRelationship;
use App\Traits\PropertyTraits\GlobalVariableRelationshipProperty;
use App\Properties\Base\BaseForwardPearsonCorrelationCoefficientProperty;
use App\Traits\PropertyTraits\IsCalculated;
use App\VariableRelationships\QMGlobalVariableRelationship;
class GlobalVariableRelationshipForwardPearsonCorrelationCoefficientProperty extends BaseForwardPearsonCorrelationCoefficientProperty
{
    use GlobalVariableRelationshipProperty;
    public $table = GlobalVariableRelationship::TABLE;
    public $parentClass = GlobalVariableRelationship::class;
    use IsCalculated;
    /**
     * @param QMGlobalVariableRelationship $model
     * @return float
     * @throws \App\Exceptions\NoUserVariableRelationshipsToAggregateException
     */
    public static function calculate($model): float{
        $val = $model->weightedAvgFromUserVariableRelationships(static::NAME);
        if($val === null){
            $val = $model->weightedAvgFromUserVariableRelationships(static::NAME);
        }
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
    public function showOnIndex(): bool {return true;}
    public function showOnDetail(): bool {return true;}
}
