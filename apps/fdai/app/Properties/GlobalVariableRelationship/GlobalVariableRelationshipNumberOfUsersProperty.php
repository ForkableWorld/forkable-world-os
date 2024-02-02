<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\GlobalVariableRelationship;
use App\Models\GlobalVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Traits\PropertyTraits\GlobalVariableRelationshipProperty;
use App\Properties\Base\BaseNumberOfUsersProperty;
use App\Traits\PropertyTraits\IsCalculated;
class GlobalVariableRelationshipNumberOfUsersProperty extends BaseNumberOfUsersProperty
{
    use GlobalVariableRelationshipProperty;
    use IsCalculated;
    public $minimum = 1;
    const MIN_FOR_REQUEST = 2;
    public $table = GlobalVariableRelationship::TABLE;
    public $parentClass = GlobalVariableRelationship::class;
    /**
     * @param GlobalVariableRelationship $model
     * @return int
     */
    public static function calculate($model): int{
        $correlations = $model->getCorrelations();
        $ids = $correlations->unique(UserVariableRelationship::FIELD_USER_ID);
        $model->setAttribute(static::NAME, $val = $ids->count());
        return $val;
    }
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {return true;}
    public function showOnDetail(): bool {return true;}
}
