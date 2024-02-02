<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Notifications;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\UI\InternalImageUrls;
use App\Utils\UrlHelper;
use Benwilkins\FCM\FcmMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use App\Slim\Model\StaticModel;
abstract class BaseNotification extends Notification
{
    use Queueable;
    const CHANNEL_DATABASE = 'database';
    const CHANNEL_BROADCAST = 'broadcast';
    const CHANNEL_FCM = 'fcm';
    const CHANNEL_SLACK = 'slack';
    /** @var BaseModel */
    protected $sourceObject;
    public $acknowledge;
    public $actions;
    public $body;
    public $click_action = '';
    public $color = "#2196F3";
    public $data = [];
    public $forceStart;
    public $icon = InternalImageUrls::ICONS_CROWDSOURCING_UTOPIA_BRAIN_01_MIKE_300X300;
    public $image = InternalImageUrls::ICONS_CROWDSOURCING_UTOPIA_BRAIN_01_MIKE_300X300;
    public $isBackground = true;  // Trying true to prevent app from opening
    public $message;
    public $msgcnt;
    public $notId;
    public $priority = FcmMessage::PRIORITY_NORMAL;
    public $sound = ''; // Optional
    public $soundName = false;
    public $title;
    public $url;
    /**
     * Create a new notification instance.
     * @param BaseModel|StaticModel|null $sourceObject
     */
    public function __construct($sourceObject = null){
        if(!$sourceObject){return;}
        foreach($sourceObject as $key => $value){
            if(property_exists($this, $key)){
                $this->$key = $value;
            }
        }
        $this->sourceObject = $sourceObject;
    }
    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable): array{
        return [
            self::CHANNEL_DATABASE,
            self::CHANNEL_BROADCAST,
            self::CHANNEL_FCM,
            //self::CHANNEL_SLACK
        ];
    }
	/**
	 * @param $notifiable
	 * @return FcmMessage
	 */
	public function toFcm($notifiable): FcmMessage{
        $message = new FcmMessage();
        $message->content([
            'title'        => $this->title,
            'body'         => $this->body,
            'sound'        => $this->sound, // Optional
            'icon'         => $this->icon, // Optional
            'click_action' => $this->url, // Optional
        ])->data($this->data)
            ->priority(FcmMessage::PRIORITY_HIGH); // Optional - Default is 'normal'.

        return $message;
    }
    /**
     * Get the mail representation of the notification.
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage{
        $m = (new MailMessage)
            ->subject($this->getTitleAttribute())
            ->line($this->getBody());
        foreach($this->getButtons() as $button){
            $m->action($button->getTitleAttribute(), $button->getUrl());
        }
        return $m;
    }
    /**
     * Get the Slack representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return SlackMessage
     */
    public function toSlack($notifiable): SlackMessage{
        $notification = $this;
        return (new SlackMessage)
            ->success()
            ->image($this->getIcon())
            ->content($notification->getBody())
            ->attachment(function ($attachment) use ($notification, $notifiable) {
                /** @var SlackAttachment $attachment */
                $attachment
                    ->title($notification->getTitleAttribute(), $notification->getUrl())
                    ->fields($notification->toArray($notifiable));
            });
    }
    public function getTitleAttribute(): string{
        return $this->title;
    }
    public function getBody(): string{
        return $this->body;
    }
    public function getIcon(): string{
        return $this->icon;
    }
    public function getUrl(array $params = []): string{
        return UrlHelper::addParams($this->url, $params);
    }
    /**
     * Get the array representation of the notification.
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable): array{
        return [
            'url'  => $this->getUrl(),
            'body' => $this->getBody(),
            'title' => $this->getTitleAttribute(),
            'icon' => $this->getIcon()
        ];
    }
    /**
     * @param string $title
     */
    public function setTitle(string $title): void{
        $this->title = $title;
    }
    /**
     * @param string $url
     */
    public function setUrl(string $url): void{
        $this->url = $url;
    }
    /**
     * @param string $message
     */
    public function setMessage(string $message): void{
        $this->message = $message;
    }
    /**
     * @param string $click_action
     */
    public function setClickAction(string $click_action): void{
        $this->click_action = $click_action;
    }
    /**
     * @param QMButton[] $actions
     */
    public function setActions(array $actions): void{
        $this->actions = $actions;
    }
    /**
     * @return BaseModel
     */
    public function getSourceObject(): BaseModel {
        return $this->sourceObject;
    }
	/**
	 * @return array
	 */
	public function getButtons(): array{
		$b = new QMButton($this->getTitleAttribute(), $this->getUrl());
		if($this->body){
			$b->setTooltip($this->getBody());
		}
		$b->setImage($this->getIcon());
		return [$b];
	}
}
