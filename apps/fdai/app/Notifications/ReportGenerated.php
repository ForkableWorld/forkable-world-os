<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Notifications;
use App\UI\ImageUrls;
use Illuminate\Bus\Queueable;
use App\Reports\AnalyticalReport;
class ReportGenerated extends BaseNotification {
    use Queueable;
    /**
     * Create a new notification instance.
     * @param AnalyticalReport $r
     */
    public function __construct(AnalyticalReport $r){
        parent::__construct($r);
    }
    public function getTitleAttribute(): string{
        return $this->getSourceObject()->getTitleAttribute();
    }
    public function getBody(): string{
        return "Click to see the new report!";
    }
    public function getIcon(): string{
        return ImageUrls::ACTIVITIES_OPEN_BOOK;
    }
    public function getUrl(array $params = []): string{
        return $this->getSourceObject()->getUrl();
    }
}
