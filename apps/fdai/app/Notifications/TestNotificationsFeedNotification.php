<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Notifications;
use App\Models\User;
use App\Utils\AppMode;
use Coreproc\AstralNotificationFeed\Notifications\AstralBroadcastMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
class TestNotificationsFeedNotification extends Notification
{
    use Queueable;
    public const LEVEL_SUCCESS = 'success';
    public const LEVEL_INFO = 'info';
    public const LEVEL_WARNING = 'warning';
    public const LEVEL_ERROR = 'error';
    protected $level = self::LEVEL_INFO;
    protected $message = '';
    /**
     * Create a new notification instance.
     * @param string $level
     * @param string $message
     */
    public function __construct(string $level = "info", string $message = 'Test message')
    {
        $this->level = $level;
        $this->message = $message;
    }
    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable): array{
        return [
            'database',
            'broadcast',
        ];
    }
    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function toArray($notifiable): array{
        return [
            'level' => $this->level,
            'message' => $this->message,
            'url' => 'https://coreproc.com',
            'target' => '_self'
        ];
    }
    /**
     * Get the broadcastable representation of the notification.
     * @param  mixed $notifiable
     * @return AstralBroadcastMessage|BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        $m = new AstralBroadcastMessage($this->toArray($notifiable));
        if(!AppMode::isApiRequest()){
            $m->onConnection('sync');
        }
        return $m;
    }
    public static function test(){
        $u = User::mike();
        $n = new static(self::LEVEL_INFO, "Test " .self::LEVEL_INFO);
        $u->notify($n);
    }
}
