<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Widgets;
use App\Storage\QueryBuilderHelper;
use App\Charts\QMHighcharts\AggregatedColumnHighstockChart;
use App\Types\QMStr;
abstract class CardChartWidget extends BaseWidget
{
    public $chartElementId = 'StringHelper::slugify($fieldTitle)."-chart-container"';
    public $color = 'blue';
    public $description = '<span class="text-success"><i class="fa fa-long-arrow-up"></i> 55% </span> increase in today sales.';
    public $fieldForChartXAxis = null;
    public $footer = '<i class="material-icons">access_time</i> updated 4 minutes ago';
    public $icon = null;
    public $table = null;
    public $title = null;
    public $url = null;
    public $queryParams = [];
    protected $chart;
    public function getCardFooterWithLink():string{
        $url = $this->getUrl();
        $description = $this->getSubtitleAttribute();
        return "
            <div class=\"card-footer\">
                <div class=\"stats\">
                    <a href=\"$url\" style=\"color: #3C4858; text-decoration: none;\">
                        <i class=\"fa fa-external-link\"></i> &nbsp; See $description
                    </a>
                </div>
            </div>
        ";
    }
    public function getLoadingText():string{
        $title = $this->getTitleAttribute();
        $description = QueryBuilderHelper::getHumanizedWhereClause($this->queryParams, $this->table);
        $description = QMStr::titleCaseSlow($description);
        return "Generating $title $description chart...";
    }
    public function getHighchart(): AggregatedColumnHighstockChart {
        if($this->chart){return $this->chart;}
        $chart = new AggregatedColumnHighstockChart($this->getTable(),
            $this->fieldForChartXAxis, $this->queryParams, $this->getTitleAttribute(), $this->getSubtitleAttribute());
        $chart->setTransparentTheme();
        $chart->setFontAwesome($this->getIcon())
            ->setFooter($this->getCardFooterWithLink())
            ->setUrl($this->getUrl());
        return $this->chart = $chart;
    }
}
