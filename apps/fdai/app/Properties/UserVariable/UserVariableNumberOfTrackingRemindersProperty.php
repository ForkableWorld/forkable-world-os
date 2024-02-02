<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\TrackingReminder;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseNumberOfTrackingRemindersProperty;
use App\Variables\QMUserVariable;
class UserVariableNumberOfTrackingRemindersProperty extends BaseNumberOfTrackingRemindersProperty
{
    use UserVariableProperty;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    use IsCalculated;
    /**
     * @param UserVariable|TrackingReminder|QMUserVariable $uv
     * @return int
     */
    public static function calculate($uv){
        if($uv instanceof QMUserVariable){$uv = $uv->l();}
        if(!$uv instanceof UserVariable){$uv = $uv->getUserVariable();}
        $old = $uv->number_of_tracking_reminders;
        $new = TrackingReminder::whereUserVariableId($uv->id)
            ->count();
        $uv->number_of_tracking_reminders = $new;
        $uv->getSubtitleAttribute();
        return $new;
    }
    public function cannotBeChangedToNull(): bool{
        return true;
        $parent = $this->getParentModel();
        if(!$parent->id){return false;}
        return parent::cannotBeChangedToNull();
    }
    public function validate(): void {
        parent::validate();
    }
}
