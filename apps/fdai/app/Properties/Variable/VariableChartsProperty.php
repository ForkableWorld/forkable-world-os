<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Charts\ChartGroup;
use App\Charts\VariableCharts\VariableChartChartGroup;
use App\Models\Variable;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseChartsProperty;
class VariableChartsProperty extends BaseChartsProperty
{
    use VariableProperty;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    /**
     * @return VariableChartChartGroup
     */
    public function getExample(): ChartGroup {
        return new VariableChartChartGroup();
    }
}
