<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Models\UserVariableClient;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseLatestSourceMeasurementStartAtProperty;
use App\Variables\QMUserVariable;
class UserVariableLatestSourceMeasurementStartAtProperty extends BaseLatestSourceMeasurementStartAtProperty
{
    use UserVariableProperty;
    use \App\Traits\PropertyTraits\IsCalculated;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    public function cannotBeChangedToNull(): bool{
        $parent = $this->getParentModel();
        if(!$parent->id){return false;}
        $uv = $this->getUserVariable();
        $num = $uv->number_of_measurements;
        return (bool)$num;
    }
    /**
     * @param UserVariable $uv
     * @return string
     */
    public static function calculate($uv){
        if($uv instanceof QMUserVariable){$uv = $uv->l();}
        //UserVariableClient::updateByUserVariable($uv);
        if($clientIds = $uv->getClientIds()){
            $at = UserVariableClient::whereUserId($uv->getUserId())
                ->whereIn(UserVariableClient::FIELD_CLIENT_ID, $clientIds)
                ->max(UserVariableClient::FIELD_LATEST_MEASUREMENT_AT);
            $uv->setAttribute(static::NAME, $at);
        } else {
            $at = $uv->latest_tagged_measurement_start_at;
            // It's probably a tag variable with no user variable clients
        }
        $previous = $uv->getAttribute(static::NAME);
        $at = datetime_or_null($at);
        $uv->setAttribute(static::NAME, $at);
        return $at;
    }
}
