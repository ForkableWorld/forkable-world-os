<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Widgets;
class OverTimeCardChartWidget extends CardChartWidget
{
    public $chartElementId = 'StringHelper::slugify($fieldTitle)."-chart-container"';
    public $color = 'blue';
    public $description = null;
    public $fieldForChartXAxis = null;
    public $footer = null;
    public $icon = null;
    public $table = null;
    public $title = null;
    public $url = null;
    public $queryParams = [];
    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run(){
        $chart = $this->getHighchart();
        return $chart->getCardHtml();
    }
}
