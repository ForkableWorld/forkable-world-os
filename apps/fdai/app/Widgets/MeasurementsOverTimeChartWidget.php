<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Widgets;
use App\Models\Measurement;
use App\UI\QMColor;
class MeasurementsOverTimeChartWidget extends OverTimeCardChartWidget
{
    public $chartElementId = 'measurements-over-time-chart-container"';
    public $color = QMColor::HEX_BLUE;
    public $description = "The number of created each day measurements. ";
    public $fieldForChartXAxis = Measurement::FIELD_START_TIME;
    public $footer = null;
    public $icon = Measurement::FONT_AWESOME;
    public $table = Measurement::TABLE;
    public $title = "Measurements per Day";
    public $url = null;
    public $queryParams = [];
}
