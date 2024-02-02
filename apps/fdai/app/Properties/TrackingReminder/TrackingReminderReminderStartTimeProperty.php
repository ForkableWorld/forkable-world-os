<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\TrackingReminder;
use App\Models\TrackingReminder;
use App\Properties\Base\BaseUserIdProperty;
use App\Traits\PropertyTraits\TrackingReminderProperty;
use App\Properties\Base\BaseReminderStartTimeProperty;
use App\Traits\PropertyTraits\IsTime;
use App\Types\QMArr;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\User\QMUser;
class TrackingReminderReminderStartTimeProperty extends BaseReminderStartTimeProperty
{
    use TrackingReminderProperty, IsTime;
    public $description = 'The time you want to be notified to track';
    public $order = "01";
    const DEFAULT_LOCAL_REMINDER_TIME = "20:00:00";
    public $default = self::DEFAULT_LOCAL_REMINDER_TIME;
    public $table = TrackingReminder::TABLE;
    public $parentClass = TrackingReminder::class;
    // See parent for $description
    public static function getDefault($data = null): ?string{
        $user = null;
        if($data instanceof QMUser){
            $user = $data;
        } else {
            $userId = BaseUserIdProperty::pluckOrDefault($data);
            if($userId){$user = QMUser::find($userId);}
        }
        if(!$user){$user = QMAuth::getQMUser();}
        return $user->localToUtcHis(self::DEFAULT_LOCAL_REMINDER_TIME);
    }
    public static function pluckUTC($data, QMUser $user = null): string {
        if(!$user){$user = QMAuth::getQMUser();}
        $local = QMArr::pluckValue($data, ['reminder_start_time_local']);
        if($local){
            return $user->localToUtcHis($local);
        }
        return static::pluckOrDefault($data);
    }
    public static function pluckOrDefault($data){
        $time = QMArr::getValue($data, [
            'reminderStartTimeEpochSeconds',
            'reminderStartTimeEpochTime'
        ]);
        if($time){ // Make sure we're getting UTC value
            // Actually I think we use local time for this return TimeHelper::toHis($time);
        }
        return parent::pluckOrDefault($data);
    }
    public function showOnUpdate(): bool {return true;}
    public function showOnCreate(): bool {return true;}
    public function showOnIndex(): bool {return true;}
    public function showOnDetail(): bool {return true;}
    public function validate(): void {
        parent::validate();
    }
}
