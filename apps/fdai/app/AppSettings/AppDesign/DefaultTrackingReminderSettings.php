<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings\AppDesign;
use App\AppSettings\AppDesign;
use App\AppSettings\AppSettings;
use App\Slim\Model\Reminders\AnonymousReminder;
use App\Slim\Model\User\QMUser;
class DefaultTrackingReminderSettings extends AppDesignSettings {
    /**
     * intro constructor.
     * @param AppSettings $appSettings
     */
    public function __construct($appSettings = null){
        if(!$appSettings){return;}
        $this->appSettings = $appSettings;
        $reminderSettings = $appSettings->appDesign->defaultTrackingReminderSettings ?? $this;
        foreach($reminderSettings as $key => $value){$this->$key = $value;}
        $this->type = $reminderSettings->type ?? 'general';
        $this->active = AppDesign::removeNullItemsFromArray($this->active);
        $this->custom = AppDesign::removeNullItemsFromArray($this->custom);
    }
    /**
     * @param QMUser $user
     * @return array
     */
    public function createReminders(QMUser $user): array {
        if(!$this->active){return [];}
        $reminderData = $this->active;
        $newReminders = [];
        foreach($reminderData as $reminderDatum){
            $newReminders[] = $user->getOrCreateReminder($reminderDatum);
        }
        return $newReminders;
    }
    /**
     * @return AnonymousReminder[]
     */
    public function getActive(): array {
        $active = parent::getActive();
        if(!$active){return [];}
        $reminders = AnonymousReminder::instantiateNonDBRows($active);
        return $reminders;
    }
}
