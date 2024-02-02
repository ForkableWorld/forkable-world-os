<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\TrackingReminder;
use App\Traits\PropertyTraits\IsPrimaryKey;
use App\Models\TrackingReminder;
use App\Traits\PropertyTraits\TrackingReminderProperty;
use App\Properties\Base\BaseIntegerIdProperty;
class TrackingReminderIdProperty extends BaseIntegerIdProperty{
	use IsPrimaryKey;
    use TrackingReminderProperty;
    public $table = TrackingReminder::TABLE;
    public $parentClass = TrackingReminder::class;
    public $image = TrackingReminder::DEFAULT_IMAGE;
    public $fontAwesome = TrackingReminder::FONT_AWESOME;
    public $isPrimary = true;
    public $autoIncrement = true;
    public const SYNONYMS = [
        'tracking_reminder_id',
        'id',
    ];
    /**
     * @return TrackingReminder
     */
    public static function getForeignClass(): string{
        return TrackingReminder::class;
    }
}
