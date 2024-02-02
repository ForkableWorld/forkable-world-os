<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\TrackingReminder;
use App\Models\TrackingReminder;
use App\Astral\Filters\ReminderNotificationsEnabledFilter;
use App\Traits\PropertyTraits\TrackingReminderProperty;
use App\Properties\Base\BaseStopTrackingDateProperty;
use App\Traits\PropertyTraits\IsDate;
use App\Types\TimeHelper;
use App\Fields\Field;
use App\Http\Requests\AstralRequest;
class TrackingReminderStopTrackingDateProperty extends BaseStopTrackingDateProperty
{
    use TrackingReminderProperty, IsDate;
    public $table = TrackingReminder::TABLE;
    public $parentClass = TrackingReminder::class;
    public function showOnUpdate(): bool {return true;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {
        $applyFilters = AstralRequest::getApplyFilters() ?? [];
        /** @var \App\Query\ApplyFilter $applyFilter */
        foreach($applyFilters as $applyFilter){
            if($applyFilter->value === ReminderNotificationsEnabledFilter::DISABLED){
                return true;
            }
        }
        return false;
    }
    public function showOnDetail(): bool {return true;}
    public function validate(): void {
        parent::validate();
        if($val = $this->getDBValue()){
            TimeHelper::assertLaterThan($val, time()-365*10*86400);
        }
    }
    public function getField($resolveCallback = null, string $name = null): Field{
        return parent::getField($resolveCallback, $name);
    }
}
