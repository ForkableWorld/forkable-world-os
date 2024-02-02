<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Metrics;
use App\Storage\QueryBuilderHelper;
use App\Traits\QMMetric;
use App\Metrics\Value;
class QMValue extends Value {
	use QMMetric;
	public function __construct(string $class, $component = null){
		$this->modelClass = $class;
		parent::__construct($component);
	}
	protected function getMetricType(): string{
		return "Value";
	}
	protected function aggregate($request, $model, $function, $column = null, $dateColumn = null){
		if(!$model){
			$model = $this->getModelInstance();
			$model = $model->newQuery();
		}
		QueryBuilderHelper::restrictQueryBasedOnPermissions($model);
		return parent::aggregate($request, $model, $function, $column, $dateColumn);
	}
}
