<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\TrackingReminder;
use App\Models\TrackingReminder;
use App\Traits\PropertyTraits\TrackingReminderProperty;
use App\Properties\Base\BaseUserVariableIdProperty;
class TrackingReminderUserVariableIdProperty extends BaseUserVariableIdProperty
{
    use TrackingReminderProperty;
    public $table = TrackingReminder::TABLE;
    public $parentClass = TrackingReminder::class;
    public function getIndexField($resolveCallback = null, string $name = null): \App\Fields\Field{
        $f = parent::getIndexField($resolveCallback, $name);
        $f->hideWhenUpdating(true);
        return $f;
    }
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {return true;}
    public function showOnDetail(): bool {return true;}
}
