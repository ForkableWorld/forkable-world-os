<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Notifications;
use App\Models\BaseModel;
use App\UI\ImageUrls;
use Illuminate\Bus\Queueable;
class AnalysisCompletedNotification extends BaseNotification
{
    use Queueable;
    private $analyzable;
    /**
     * Create a new notification instance.
     * @param $analyzable
     */
    public function __construct(BaseModel $analyzable){
        parent::__construct($analyzable);
    }
    public function getTitleAttribute(): string{
        return $this->getSourceObject()->getTitleAttribute()." Analysis Completed!";
    }
    public function getBody(): string{
        return "Click to see results!";
    }
    public function getIcon(): string{
        return ImageUrls::ANALYSIS;
    }
    public function getUrl(array $params = []): string{
        return $this->getSourceObject()->getUrl();
    }
}
