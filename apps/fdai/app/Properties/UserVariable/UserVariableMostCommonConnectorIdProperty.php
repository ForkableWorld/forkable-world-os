<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\Measurement;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseMostCommonConnectorIdProperty;
use App\Variables\QMUserVariable;
class UserVariableMostCommonConnectorIdProperty extends BaseMostCommonConnectorIdProperty
{
    use UserVariableProperty;
    use IsCalculated;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    /**
     * @param QMUserVariable $uv
     * @return mixed
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public static function calculate($uv): ?int {
        $mostCommon = $uv->mostCommonFromMeasurementsWithTags(Measurement::FIELD_CONNECTOR_ID);
        $uv->setAttribute(static::NAME, $mostCommon);
        return $mostCommon;
    }
}
