<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Widgets;
use App\UI\FontAwesome;
use App\UI\QMColor;
class CompletedAnalysesChartWidget extends OverTimeCardChartWidget
{
    public $chartElementId = 'completed-analyses-chart';
    public $color = QMColor::HEX_GREEN;
    public $description = "The Number of Analyses Completed Each Day";
    public $fieldForChartXAxis = "analysis_ended_at";
    public $footer = null;
    public $icon = FontAwesome::CALCULATOR_SOLID;
    public $table = null;
    public $title = null;
    public $url = null;
    public $queryParams = ["analysis_ended_at" => "NOT NULL"];

}
