<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\Events\JobFailed;
class JobFailedNotification extends BaseNotification
{
    use Queueable;
    /** @var JobFailed */
    private $jobFailed;
    /**
     * Create a new notification instance.
     * @param JobFailed $event
     */
    public function __construct(JobFailed $event){
        $this->jobFailed = $event;
        $this->body = $event->job->getName() ." FAILED on connection ".$event->connectionName;
        $this->title = $event->job->getName() ." FAILED";
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
        return $this->url;
    }
}
