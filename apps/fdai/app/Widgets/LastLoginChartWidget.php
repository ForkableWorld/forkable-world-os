<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Widgets;
use App\Models\User;
use App\UI\FontAwesome;
use App\UI\QMColor;
class LastLoginChartWidget extends OverTimeCardChartWidget
{
    public $chartElementId = 'measurements-over-time-chart-container"';
    public $color = QMColor::HEX_BLUE;
    public $description = "The number of created each day measurements. ";
    public $fieldForChartXAxis = User::FIELD_LAST_LOGIN_AT;
    public $footer = null;
    public $icon = FontAwesome::SIGN_IN_ALT_SOLID;
    public $table = User::TABLE;
    public $title = "Last Login by Day";
    public $url = null;
    public $queryParams = [];
    public function toArray(): array{
        $this->url = User::generateDataLabIndexUrl(['sort' => User::FIELD_LAST_LOGIN_AT]);
        return parent::toArray();
    }
}
