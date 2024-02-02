<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\TrackingReminder;
use App\Logging\QMLog;
use App\Models\TrackingReminder;
use App\Traits\PropertyTraits\TrackingReminderProperty;
use App\Properties\Base\BaseImageUrlProperty;
use App\UI\ImageHelper;
use App\Slim\Model\Reminders\QMTrackingReminder;
class TrackingReminderImageUrlProperty extends BaseImageUrlProperty
{
    use TrackingReminderProperty;
    public $table = TrackingReminder::TABLE;
    public $parentClass = TrackingReminder::class;
    public static function updateTrackingReminderImages(){
        QMLog::infoWithoutContext(__FUNCTION__);
        $remindersThatNeedImages = QMTrackingReminder::getTrackingReminders(null, [
            'variableCategoryId' => 13,
            'trackingReminderImageUrl' => null,
            'limit' => 0
        ]);
        foreach ($remindersThatNeedImages as $trackingReminder) {
            $imageUrl = ImageHelper::getDrugImageUrl($trackingReminder->variableName, $trackingReminder->getDefaultValueInUserUnit());
            if ($imageUrl === false) {
                QMLog::error("Drug image api return false.  Maybe API is down.");
                continue;
            }
            if (!isset($imageUrl)) {
                $imageUrl = 'Not Found';
            }
            TrackingReminder::whereId($trackingReminder->id)
                ->whereNull('image_url')
                ->update(['image_url' => $imageUrl]);
        }
    }
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {return false;}
    public function showOnDetail(): bool {return false;}
}
