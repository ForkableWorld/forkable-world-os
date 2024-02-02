<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Intents;
use App\Properties\Base\BaseUnitIdProperty;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Slim\Model\QMUnit;
class TrackingReminderNotificationIntent extends QMIntent {
    public $actionName = 'tracking_reminder_notification';
    public $notificationAction;
    public $value;
    public function __construct(){
        parent::__construct();
    }
    /**
     * @return void
     * @throws \App\Exceptions\TrackingReminderNotificationNotFoundException
     */
    public function fulfillIntent(){
        $this->saveNotificationResponse();
    }
    /**
     * @return int|null
     */
    public function getTrackingReminderNotificationId(){
        $id = $this->getContextParam('trackingReminderNotificationId');
        return $id;
    }
    /**
     * @return bool|QMTrackingReminderNotification
     * @throws \App\Exceptions\TrackingReminderNotificationNotFoundException
     */
    protected function saveNotificationResponse(){
        $params = $this->getContextParams();
        $id = $this->getTrackingReminderNotificationId();
        if($id){
            $params['modifiedValue'] = $this->getValue();
            $params['notificationAction'] = $params['action'] = $this->getNotificationAction();
            $notificationFromDB = QMTrackingReminderNotification::getByTrackingReminderNotificationId($id, true);
            $notificationFromDB->action = $params["action"] ?? $params["notificationAction"] ?? null;
            $handled = QMTrackingReminderNotification::handleSubmittedNotification($params);
            // Doesn't seem to work
            $this->getWebhookClient()->clearOutgoingContext(self::CONTEXT_tracking_reminder_notification);
            $this->addMessage($handled->getResponseSentence());
            return $this->setSubmittedNotification($handled);
        }
        return false;
    }
    /**
     * @return null|string
     */
    protected function getNotificationAction(){
        $OriginalRequest = $this->getWebhookClient()->getOriginalRequest();
        if(isset($OriginalRequest["payload"]["inputs"][0]["rawInputs"][0]["query"])){
            $query = strtolower($OriginalRequest["payload"]["inputs"][0]["rawInputs"][0]["query"]);
            if($query && in_array($query, QMTrackingReminderNotification::getAvailableNotificationActions(), true)){
                return $query;
            }
        }
        $param = $this->getContextParam('notificationAction');
        if($param && in_array($param, QMTrackingReminderNotification::getAvailableNotificationActions(), true)){
            return $param;
        }
        $query = $this->getQuery(true);
        if($query && in_array($query, QMTrackingReminderNotification::getAvailableNotificationActions(), true)){
            return $query;
        }
        $value = $this->getContextParam('value');
        if($value && in_array($value, QMTrackingReminderNotification::getAvailableNotificationActions(), true)){
            return $value;
        }
        $value = $this->getContextParam('modifiedValue');
        if($value && in_array($value, QMTrackingReminderNotification::getAvailableNotificationActions(), true)){
            return $value;
        }
        return QMTrackingReminderNotification::TRACK;
    }
    /**
     * @return float|mixed|string|null
     */
    protected function getValue(){
        $value = $this->getContextParam('value');
        $modifiedValue = $this->getContextParam('modifiedValue');
        $originalRequest = $this->getWebhookClient()->getOriginalRequest();
        $query = $this->getQuery(true);
        $action = $this->getNotificationAction();
        $unit = $this->getUnit();
        if($value === null){
            $value = $modifiedValue;
        }
        if(($value === null) && isset($originalRequest["payload"]["inputs"][0]["rawInputs"][0]["query"])){
            $value = $originalRequest["payload"]["inputs"][0]["rawInputs"][0]["query"];
            if(strpos($value, ' ')){
                $both = explode(' ', $value);
                if($both[0] === $both[1]){
                    $value = $both[0]; // User repeated himself
                }
            }
        }
        if($value === null && is_numeric($query)){
            $value = (float)$query;
        }
        //if($value === null && $query === "yes"){$value = 1;}
        //if($value === null && $query === "no"){$value = 0;}
        if($value === null && $action !== null && is_numeric($action)){
            $value = (float)$action;
        }
        if($value && $unit && is_string($value)){
            $value = trim(str_ireplace($unit->abbreviatedName, '', $value));
        }
        if(is_numeric($value)){
            $value = (float)$value;
        }
        if($value && in_array(strtolower($value), QMTrackingReminderNotification::getAvailableNotificationActions(), true)){
            return null;
        }
        return $value;
    }
    /**
     * @return QMUnit
     */
    public function getUnit(): QMUnit{
        $unit = BaseUnitIdProperty::pluckParentDBModel($this->getContextParams(), false);
        return $unit;
    }
    /**
     * @param QMTrackingReminderNotification|QMTrackingReminderNotification $submittedNotification
     * @return QMTrackingReminderNotification|QMTrackingReminderNotification
     */
    public function setSubmittedNotification($submittedNotification){
        return $this->submittedNotification = $submittedNotification;
    }
}
