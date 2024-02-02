<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipDataSourceNameProperty;
use App\Properties\Base\BaseDataSourceNameProperty;
use App\Traits\PropertyTraits\CorrelationProperty;
class CorrelationDataSourceNameProperty extends BaseDataSourceNameProperty
{
    use CorrelationProperty;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;

    /**
     * @return int
     */
    public static function fixDataSourceNamesForUserVariableRelationships()
    {
        $names = BaseDataSourceNameProperty::get3rdPartyDataSourceNames();
        $result = QMUserVariableRelationship::writable()
            ->whereNotIn(UserVariableRelationship::FIELD_DATA_SOURCE_NAME, $names)
            ->update([UserVariableRelationship::FIELD_DATA_SOURCE_NAME => GlobalVariableRelationshipDataSourceNameProperty::DATA_SOURCE_NAME_USER]);
        return $result;
    }
}
