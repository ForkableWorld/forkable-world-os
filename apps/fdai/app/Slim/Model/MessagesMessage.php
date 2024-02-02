<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model;
use App\Models\WpBpMessagesMessage;
use App\Slim\Middleware\QMAuth;
use App\Buttons\Message\ArchiveMessageButton;
use App\Buttons\Message\ReplyMessageButton;
use App\Utils\IonicHelper;
use App\Slim\Model\Notifications\PushNotificationData;
use App\Slim\Model\User\QMUser;
use stdClass;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
class MessagesMessage extends DBModel {
    public const UNIQUE_INDEX_COLUMNS = WpBpMessagesMessage::UNIQUE_INDEX_COLUMNS;
    public const TABLE = 'wp_bp_messages_messages';
    public const FIELD_DATE_SENT = 'date_sent';
    public const FIELD_id = 'id';
    public const FIELD_message = 'message';
    public const FIELD_SENDER_ID = 'sender_id';
    public const FIELD_subject = 'subject';
    public const FIELD_THREAD_ID = 'thread_id';
    protected static $FOREIGN_KEYS = [];
    public $buttons;
    public $dateSent;
    public $id;
    public $message;
    public $recipients;
    public $senderId;
    public $subject;
    public $threadId;
    public static $FIELD_NAME = [
        'id'        => 'id',
        'thread_id' => 'thread_id',
        'sender_id' => 'sender_id',
        'subject'   => 'subject',
        'message'   => 'message',
        'date_sent' => 'date_sent'
    ];
    public static $PRIMARY_KEY = ['id' => 'id'];
    /**
     * @param int $recipientUserId
     * @return MessagesMessage[]
     */
    public static function getMessagesForRecipient(int $recipientUserId = null){
        if(!$recipientUserId){
            $recipientUserId = QMAuth::getUser()->id;
        }
        $rows = self::readonly()->where(MessagesRecipient::TABLE.'.'.MessagesRecipient::FIELD_USER_ID, $recipientUserId)
            ->join(MessagesRecipient::TABLE, self::TABLE.'.'.self::FIELD_THREAD_ID, '=', MessagesRecipient::TABLE.'.'.MessagesRecipient::FIELD_thread_id)
            ->getArray();
        if(!$rows){
            return $rows;
        }
        $models = static::convertRowsToModels($rows, true);
        return $models;
    }
    /**
     * @param string|null $reason
     * @return bool
     */
    public function save(string $reason = null): bool {
        $messagesRecipients = $this->getMessagesRecipients();
        foreach($messagesRecipients as $messageRecipient){
            $messageRecipient->setThreadId($this->getThreadId());
            $messageRecipient->save();
        }
        return parent::save();
    }
    /**
     * @param int id
     * @return int
     */
    public function setId($id){
        $originalValue = $this->id;
        if($originalValue !== $id){
            $this->modifiedFields['id'] = 1;
        }
        return $this->id = $id;
    }
    /**
     * @param int threadId
     * @return int
     */
    public function setThreadId(int $threadId){
        $originalValue = $this->threadId;
        if($originalValue !== $threadId){
            $this->modifiedFields['thread_id'] = 1;
        }
        return $this->threadId = $threadId;
    }
    /**
     * @param int senderId
     * @return int
     */
    public function setSenderId(int $senderId){
        $originalValue = $this->senderId;
        if($originalValue !== $senderId){
            $this->modifiedFields['sender_id'] = 1;
        }
        return $this->senderId = $senderId;
    }
    /**
     * @param string subject
     * @return string
     */
    public function setSubject(string $subject){
        $originalValue = $this->subject;
        if($originalValue !== $subject){
            $this->modifiedFields['subject'] = 1;
        }
        return $this->subject = $subject;
    }
    /**
     * @param string message
     * @return string
     */
    public function setMessage(string $message){
        $originalValue = $this->message;
        if($originalValue !== $message){
            $this->modifiedFields['message'] = 1;
        }
        return $this->message = $message;
    }
    /**
     * @param string dateSent
     * @return string
     */
    public function setDateSent(string $dateSent){
        $originalValue = $this->dateSent;
        if($originalValue !== $dateSent){
            $this->modifiedFields['date_sent'] = 1;
        }
        return $this->dateSent = $dateSent;
    }
    /**
     * @return int
     */
    public function getId(): int{
        $id = $this->id;
        return (int)$id;
    }
    /**
     * @return int
     */
    public function getThreadId(): int{
        $threadId = $this->threadId;
        return (int)$threadId;
    }
    /**
     * @return int
     */
    public function getSenderId(): int{
        $senderId = $this->senderId;
        return (int)$senderId;
    }
    /**
     * @return string
     */
    public function getSubject(): string{
        $subject = $this->subject;
        return (string)$subject;
    }
    /**
     * @return string
     */
    public function getMessage(): string{
        $message = $this->message;
        return (string)$message;
    }
    /**
     * @return string
     */
    public function getDateSent(): string{
        $dateSent = $this->dateSent;
        return (string)$dateSent;
    }
    /**
     * @return array
     */
    public function sendPushNotifications(){
        $recipients = $this->getMessagesRecipients();
        if(!$recipients){
            throw new BadRequestHttpException("Please provide recipients property containing user_id");
        }
        $results = [];
        $pushData = $this->getPushNotificationData();
        foreach($recipients as $recipient){
            $user = $recipient->getQMUser();
            $results[$recipient->getUserId()] = $user->notifyByPushData($pushData);
        }
        return $results;
    }
    /**
     * @return MessagesRecipient[]
     */
    private function getMessagesRecipients(){
        $recipients = $this->recipients;
        $recipientsArray = [];
        if(is_object($recipients) || (isset($recipients[0]) && $recipients[0] instanceof stdClass)){
            foreach($recipients as $recipientUserId => $recipientData){
                $recipientsArray[] = MessagesRecipient::instantiateIfNecessary($recipientData);
            }
        }else{
            $recipientsArray = $recipients;
        }
        return $this->recipients = $recipientsArray;
    }
    /**
     * @return PushNotificationData
     */
    public function getPushNotificationData(){
        $data = new PushNotificationData();
        $data->setTitle($this->getSubject());
        $data->setMessage($this->getMessage());
        $data->setForceStart(1);
        $data->setNotId($this->getId());
        $sender = $this->getSender();
        $image = $sender->getAvatar();
        $data->setImage($image);
        $data->setUrl(IonicHelper::getChatUrl($data->toArray()));
        $buttons = $this->getButtons();
        $data->setActions($buttons);
        return $data;
    }
    /**
     * @return QMUser
     */
    public function getSender(){
        return QMUser::find($this->getSenderId());
    }
    /**
     * @return QMButton[]
     */
    public function  setDefaultButtons(): array{
        $buttons = [];
        //$buttons[] = new SnoozeMessageButton($this);
        $buttons[] = new ArchiveMessageButton($this);
        $buttons[] = new ReplyMessageButton($this);
        return $this->buttons = $buttons;
    }
}
