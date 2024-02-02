<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\TrackingReminder;
use App\Models\TrackingReminder;
use App\Traits\ForeignKeyIdTrait;
use App\Traits\PropertyTraits\TrackingReminderProperty;
use App\Properties\Base\BaseVariableIdProperty;
use App\Variables\QMCommonVariable;
class TrackingReminderVariableIdProperty extends BaseVariableIdProperty
{
    use TrackingReminderProperty, ForeignKeyIdTrait;
    public $table = TrackingReminder::TABLE;
    public $parentClass = TrackingReminder::class;
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
        $v = QMCommonVariable::find($this->getDBValue());
        $manual = $v->getManualTracking();
        if(strpos($v->getNameAttribute(), ' Activities') !== false){
            $this->throwException("we should not have reminders for Activities but we have one for ".$v->getNameAttribute());
        }
        if($manual === false){
            $manual = $v->getManualTracking();
            $this->throwException("reminders should only be created for ManualTracking variables but this one is for $v->name");
        }
    }
    public function showOnUpdate(): bool {return true;}
    public function showOnCreate(): bool {return true;}
    public function showOnIndex(): bool {return true;}
    public function showOnDetail(): bool {return true;}
}
