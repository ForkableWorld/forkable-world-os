<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Notifications;
use App\Models\Notification;
use App\Models\User;
use App\Types\QMStr;
use App\Utils\AppMode;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Support\Arrayable;
class MiroNotification extends \Illuminate\Notifications\Notification implements Arrayable
{
    use Queueable;
    public const LEVEL_SUCCESS = 'success';
    public const LEVEL_INFO = 'info';
    public const LEVEL_WARNING = 'warning';
    public const LEVEL_ERROR = 'error';
    protected $level = self::LEVEL_INFO; // Notification level - info, success or error
    protected $title;
    protected $subtitle;
    protected $url;
    /**
     * @var User
     */
    private $user;
    /**
     * @var string|null
     */
    private $fontAwesome;
    /**
     * Create a new notification instance.
     * @param string $title
     * @param string $level
     * @param string|null $fontAwesome
     * @param string|null $url
     */
    public function __construct(string $title,
                                string $level = self::LEVEL_INFO,
                                string $fontAwesome = null,
                                string $url = null){
        $this->level = $level;
        $this->fontAwesome = $fontAwesome;
        $this->url = $url;
        if (!empty($title)) {
            $this->title($title);
        }

        if (!empty($subtitle)) {
            $this->subtitle($subtitle);
        }

        $this
            ->showMarkAsRead()
            ->showCancel()
            ->createdAt(Carbon::now());
    }
    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable): array{
        return ['database', 'broadcast'];
    }
    /**
     * Get the array representation of the notification.
     * @return array
     */
    public function toArray(): array{
        $n = $this->notification;
        foreach ($this as $key => $value) {
            if (!empty($value) && $key !== 'notification') {
                $n[QMStr::camelize($key)] = $value;
            }
        }
        return $n;
    }
    public static function test(){
        $u = User::mike();
        $u->notify(new static("message"));
    }
    /**
     * Get the broadcastable representation of the notification.
     * @param  mixed $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        $m = new BroadcastMessage($this->toArray($notifiable));
        if(!AppMode::isApiRequest()){$m->onConnection('sync');}
        $m->onConnection('sync'); // TODO: More robust queue setup
        return $m;
    }
    /**
     * @return string
     */
    public function getLevel(): string{
        return $this->level;
    }
    /**
     * @return string
     */
    public function getTitleAttribute(): string{
        return $this->title;
    }
    /**
     * @return mixed
     */
    public function getSubtitle(): ?string {
        return $this->subtitle;
    }
    /**
     * @return mixed
     */
    public function getUrl(): ?string {
        return $this->url;
    }
    /**
     * @return string|null
     */
    public function getFontAwesome(): ?string{
        return $this->fontAwesome;
    }




    const LEVELS = ['info', 'success', 'error'];

    protected $notification = [];



    public static function make(string $title = null, string $subtitle = null): self
    {
        return new static($title, $subtitle);
    }

    public function title(string $value): self
    {
        $this->title = $this->notification['title'] = $value;
        return $this;
    }

    public function subtitle(string $value): self
    {
        $this->subtitle = $this->notification['subtitle'] = $value;
        return $this;
    }

    public function link(string $value, bool $external = false): self
    {
        $this->url = $this->notification['url'] = $value;
        $this->notification['external'] = $external;
        return $this;
    }

    public function route(string $name, string $resourceName, $resourceId = null): self
    {
        $this->notification['external'] = false;
        $this->notification['route'] = [
            'name' => $name,
            'params' => ['resourceName' => $resourceName]
        ];

        if (!empty($resourceId)) {
            $this->notification['route']['params']['resourceId'] = $resourceId;
        }

        return $this;
    }

    public function routeIndex(string $resourceName): self
    {
        return $this->route('index', $resourceName);
    }

    public function routeCreate(string $resourceName): self
    {
        return $this->route('create', $resourceName);
    }

    public function routeEdit(string $resourceName, $resourceId): self
    {
        return $this->route('edit', $resourceName, $resourceId);
    }

    public function routeDetail(string $resourceName, $resourceId): self
    {
        return $this->route('detail', $resourceName, $resourceId);
    }

    public function level(string $value): self
    {
        if (!in_array($value, static::LEVELS)) {
            $value = 'info';
        }

        $this->notification['level'] = $value;
        return $this;
    }

    public function info(string $value): self
    {
        return $this
            ->title($value)
            ->level('info');
    }

    public function success(string $value): self
    {
        return $this
            ->title($value)
            ->level('success');
    }

    public function error(string $value): self
    {
        return $this
            ->title($value)
            ->level('error');
    }

    public function createdAt(Carbon $value): self
    {
        $this->notification['created_at'] = $value->toAtomString();
        return $this;
    }

    public function icon(string $value): self
    {
        $this->notification['icon'] = $value;
        return $this;
    }

    public function showMarkAsRead(bool $value = true): self
    {
        $this->notification['show_mark_as_read'] = $value;
        return $this;
    }

    public function showCancel(bool $value = true): self
    {
        $this->notification['show_cancel'] = $value;
        return $this;
    }

    public function showOpen(bool $value = false): self
    {
        $this->notification['show_open'] = $value;
        return $this;
    }

    public function cancelText(string $value = "Cancel"): self
    {
        $this->notification['cancel_text'] = $value;
        return $this;
    }

    public function addButton(string $text, string $url): self
    {
        $this->notification['buttons'][] = ['text' => $text, 'url' => $url];
        return $this;
    }

}
