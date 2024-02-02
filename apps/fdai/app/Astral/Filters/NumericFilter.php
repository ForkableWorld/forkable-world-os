<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Astral\Filters;
use App\Models\BaseModel;
use App\Properties\BaseProperty;
use App\Slim\Middleware\QMAuth;
use App\Types\QMStr;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use philperusse\Filters\ColumnFilter;
class NumericFilter extends ColumnFilter {
	/**
	 * @var BaseModel|BaseProperty
	 */
	protected $model;
	/**
	 * @param BaseModel $model
	 */
	public function __construct(BaseModel $model){
		$this->model = $model;
	}
	/**
	 * Apply the filter to the given query.
	 * @param Request $request
	 * @param Builder $query
	 * @param mixed $value
	 * @return Builder
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function apply(Request $request, $query, $value): Builder{
		$args = collect($value)->values();
		// TODO: Why ->filter()?  It removes 0's? ->filter(); //Remove any empty keys.
		if($args->isEmpty()){
			return $query;
		}
		$col = $value["column"] ?? null;
		$op = $value["operator"] ?? null;
		$val = $value["data"] ?? null;
		if(QMStr::isNullString($val)){
			if($op === "="){
				return $query->whereNull($col);
			}
			if($op === "<>"){
				return $query->whereNotNull($col);
			}
		}
		return $query->where(...$args->all());
	}
	public function columns(): array{
		$model = $this->getModel();
		/** @var BaseProperty[] $props */
		$props = array_merge($model->getNumericPropertyModels(), $model->getDateTimePropertyModels());
		if(QMAuth::isAdmin()){
			if($model->hasColumn('client_id')){
				$clientId = $model->getPropertyModel('client_id');
				$props[] = $clientId;
			}
		}
		$columns = [];
		foreach($props as $prop){
			$field = $prop->getField();
			$columns[$field->attribute] = $field->name;
		}
		asort($columns);
		return $columns;
	}
	/**
	 * @return BaseModel
	 */
	public function getModel(): BaseModel{
		return $this->model;
	}
}
