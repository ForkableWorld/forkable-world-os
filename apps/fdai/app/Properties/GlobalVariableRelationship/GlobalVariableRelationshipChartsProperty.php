<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\GlobalVariableRelationship;
use App\Charts\GlobalVariableRelationshipCharts\GlobalVariableRelationshipChartGroup;
use App\Charts\ChartGroup;
use App\Models\GlobalVariableRelationship;
use App\Properties\Base\BaseChartsProperty;
use App\Traits\PropertyTraits\GlobalVariableRelationshipProperty;
use App\Traits\PropertyTraits\IsCalculated;
class GlobalVariableRelationshipChartsProperty extends BaseChartsProperty
{
    use GlobalVariableRelationshipProperty;
    use IsCalculated;
    public $table = GlobalVariableRelationship::TABLE;
    public $parentClass = GlobalVariableRelationship::class;
    /**
     * @return GlobalVariableRelationshipChartGroup
     */
    public function getExample(): ChartGroup{
        return new GlobalVariableRelationshipChartGroup();
    }
	/**
	 * @param $model
	 * @return mixed
	 */
	public static function calculate($model){
        $model->getOrSetCharts()->setHighchartConfigs();
        $model->setAttribute(static::NAME, $charts = $model->getOrSetCharts());
        return $charts;
    }
}
