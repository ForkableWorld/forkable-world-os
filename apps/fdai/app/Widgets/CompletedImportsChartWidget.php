<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Widgets;
use App\Models\ConnectorImport;
use App\UI\FontAwesome;
use App\UI\QMColor;
class CompletedImportsChartWidget extends OverTimeCardChartWidget
{
    public $chartElementId = 'completed-imports-chart';
    public $color = QMColor::HEX_BLUE;
    public $description = "The Number of Analyses Completed Each Day";
    public $fieldForChartXAxis = "analysis_ended_at";
    public $footer = null;
    public $icon = FontAwesome::CALCULATOR_SOLID;
    public $table = ConnectorImport::TABLE;
    public $title = "Imports per Day";
    public $url = null;
    public $queryParams = [ConnectorImport::FIELD_IMPORT_ENDED_AT => "NOT NULL"];
}
