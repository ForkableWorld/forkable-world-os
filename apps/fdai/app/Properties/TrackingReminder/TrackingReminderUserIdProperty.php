<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\TrackingReminder;
use App\Models\TrackingReminder;
use App\Traits\PropertyTraits\TrackingReminderProperty;
use App\Properties\Base\BaseUserIdProperty;
use App\Traits\HasUserFilter;
use App\Fields\Field;
class TrackingReminderUserIdProperty extends BaseUserIdProperty
{
    use TrackingReminderProperty;
    use HasUserFilter;
    public $table = TrackingReminder::TABLE;
    public $parentClass = TrackingReminder::class;
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {return false;}
    public function showOnDetail(): bool {return true;}
    public function getField($resolveCallback = null, string $name = null): Field{
        return parent::getField($resolveCallback, $name);
    }
}
