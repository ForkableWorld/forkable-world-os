<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\Events\JobProcessing;
class JobStartedNotification extends BaseNotification
{
    use Queueable;
    /** @var JobProcessing */
    private $jobProcessing;
    /**
     * Create a new notification instance.
     * @param JobProcessing $event
     */
    public function __construct(JobProcessing $event = null){
        $this->jobProcessing = $event;
        $this->body = $event->job->getName() ." started on connection ".$event->connectionName;
        $this->title = $event->job->getName() ." COMPLETED";
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
