<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Notifications;
use App\UI\ImageUrls;
class TestGeneratedNotification extends BaseNotification
{
    /**
     * Create a new notification instance.
     * @param string $url
     * @param string $testName
     */
    public function __construct(string $url, string $testName){
        $this->setTitle("Generated $testName");
        $this->setUrl($url);
		$this->image = ImageUrls::DEVELOPMENT_055_BROKEN_CODE;
		$this->body = "Click to open in PHPStorm";
        parent::__construct();
    }
    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable): array{
        return [
            self::CHANNEL_FCM,
            self::CHANNEL_BROADCAST,
        ];
    }
    public function getTitleAttribute(): string{
        return $this->title;
    }
    public function getBody(): string{
        return "Click to go to test!";
    }
    public function getIcon(): string{
        return ImageUrls::PHPUNIT;
    }
    public function getUrl(array $params = []): string{
        return $this->url;
    }
}
