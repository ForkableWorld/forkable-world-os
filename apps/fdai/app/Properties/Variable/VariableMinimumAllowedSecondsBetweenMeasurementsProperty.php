<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\Variable;
use App\Properties\Base\BaseMinimumAllowedSecondsBetweenMeasurementsProperty;
use App\Traits\PropertyTraits\VariableProperty;
use App\Variables\QMUserVariable;
class VariableMinimumAllowedSecondsBetweenMeasurementsProperty
	extends BaseMinimumAllowedSecondsBetweenMeasurementsProperty {
	use VariableProperty;
	public $table = Variable::TABLE;
	public $parentClass = Variable::class;
	/**
	 * @param Variable|\App\Variables\QMVariable|array $v
	 * @return int
	 */
	public static function pluckOrDefault($v): ?int{
        if(is_array($v)){
            return parent::pluckOrDefault($v);
        }
		if($v instanceof QMUserVariable){
			if($min = $v->minimumAllowedSecondsBetweenMeasurements){
				return $min;
			}
			$v = $v->getVariable();
			return $v->getMinimumAllowedSecondsBetweenMeasurementsAttribute();
		}
		$n = $v->getVariableName();
		$min = null;
		if(stripos($n, 'hourly') !== false){
			$min = 3600;
		}
		if(stripos($n, 'daily') !== false){
			$min = 86400;
		}
		if(str_contains($n, 'Quarterly Average')){
			$min = 86400;
		}
		if(!$min){
			$category = $v->getQMVariableCategory();
			$min = $category->getMinimumAllowedSecondsBetweenMeasurements();
		}
		if(!$min){
			$min = 1;
		} //if(!$min){$min = 60;}  // TODO: Uncomment and fix tests so we prevent more than 1 measurement per minute
		return $min;
	}
}
