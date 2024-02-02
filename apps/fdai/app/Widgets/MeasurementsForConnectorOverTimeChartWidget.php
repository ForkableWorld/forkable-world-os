<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Widgets;
use App\Models\Measurement;
class MeasurementsForConnectorOverTimeChartWidget extends OverTimeCardChartWidget
{
    public $chartElementId = 'StringHelper::slugify($fieldTitle)."-chart-container"';
    public $color = 'blue';
    public $description = "The number of measurements imported from this data source over time. ";
    public $fieldForChartXAxis = Measurement::FIELD_START_TIME;
    public $footer = null;
    public $icon = Measurement::FONT_AWESOME;
    public $table = Measurement::TABLE;
    public $title = null;
    public $url = null;
    public $queryParams = [];
    public static function getWidgetParamsByModel($connector): array {
        $m = new static();
        $m->title = $connector->display_name." Measurements";
        $m->queryParams = ['connector_id' => $connector->id];
        $m->url = Measurement::generateDataLabIndexUrl([Measurement::FIELD_CONNECTOR_ID => $connector->id]);
	    $toArray = $m->toArray();
	    return ['name' => static::class, 'params' => $toArray];
    }
}
