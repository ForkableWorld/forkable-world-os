<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Widgets;
use App\Models\Connection;
use App\Models\ConnectorImport;
use App\UI\FontAwesome;
use App\UI\QMColor;
class NeverImportedChartWidget extends OverTimeCardChartWidget
{
    public $chartElementId = 'never-imported-chart';
    public $color = QMColor::HEX_RED;
    public $description = "Connections to third party data sources that have never been imported from charted over their initial connection time. ";
    public $fieldForChartXAxis = Connection::CREATED_AT;
    public $footer = null;
    public $icon = FontAwesome::BATTERY_EMPTY_SOLID;
    public $table = Connection::TABLE;
    public $title = "Never Imported";
    public $url = null;
    public $queryParams = [
        ConnectorImport::FIELD_IMPORT_ENDED_AT => "NULL"
    ];
}
