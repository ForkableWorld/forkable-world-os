<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Notifications;
use App\Buttons\QMButton;
use Illuminate\Bus\Queueable;
class LinkNotification extends BaseNotification
{
    use Queueable;
    /**
     * Create a new notification instance.
     * @param string $title
     * @param string $url
     * @param string|null $body
     * @param string|null $icon
     */
    public function __construct(string $title, string $url, string $body = null, string $icon = null){
        parent::__construct();
        $this->title = $title;
        $this->url = $url;
        $this->body = $body;
        $this->icon = $icon;
    }
}
