<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\Events\JobProcessed;
class JobCompletedNotification extends BaseNotification
{
    use Queueable;
    /** @var JobProcessed */
    private $jobProcessed;
    /**
     * Create a new notification instance.
     * @param JobProcessed $event
     */
    public function __construct(JobProcessed $event){
        $this->jobProcessed = $event;
        $this->body = $this->jobProcessed->job->getName() ." COMPLETED on connection ".$this->jobProcessed->connectionName;
        $this->title = $this->jobProcessed->job->getName() ." COMPLETED";
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
