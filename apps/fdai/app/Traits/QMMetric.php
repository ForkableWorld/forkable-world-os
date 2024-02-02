<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Models\BaseModel;
use App\Storage\QueryBuilderHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
trait QMMetric {
	protected $modelClass;
	public function getModelInstance(): BaseModel{
		$class = $this->getModelClass();
		if(!$class){
			le('!$class');
		}
		return new $class();
	}
	/**
	 * @return BaseModel|string
	 */
	public function getModelClass(): string{
		return $this->modelClass;
	}
	public function qb(): Builder{
		$qb = $this->getModelInstance()->newQuery();
		QueryBuilderHelper::restrictQueryBasedOnPermissions($qb);
		return $qb;
	}
	/**
	 * Get the URI key for the metric.
	 * @return string
	 */
	public function uriKey(): string{
		$model = $this->getModelInstance();
		$slugClass = $model->getSlugifiedTableName();
		$metricName = $this->getSlugifiedMetricName();
		return $slugClass . '-' . $metricName;
	}
	/**
	 * Get the displayable name of the metric.
	 * @return string
	 */
	public function name(): string{
		$name = parent::name();
		$type = $this->getMetricType();
		$name = str_replace(" $type", "", $name);
		return $this->name ?: $name;
	}
	private function getSlugifiedMetricName(): string{
		return Str::slug($this->name());
	}
	abstract protected function getMetricType(): string;
}
