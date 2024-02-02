<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Widgets;
use App\Models\WpPost;
use App\UI\QMColor;
class PostsUpdatedChartWidget extends OverTimeCardChartWidget
{
    public $chartElementId = 'posts-updated-chart-container"';
    public $color = QMColor::HEX_BLUE;
    public $description = "The number of posts updated each day. ";
    public $fieldForChartXAxis = WpPost::FIELD_POST_MODIFIED;
    public $footer = null;
    public $icon = WpPost::FONT_AWESOME;
    public $table = WpPost::TABLE;
    public $title = "Posts Updated per Day";
    public $url = null;
    public $queryParams = [];
}
