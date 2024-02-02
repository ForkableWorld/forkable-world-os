<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Filters;
use App\Models\Variable;
use App\Variables\QMVariableCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Filters\Filter;
class VariableCategoryFilter extends Filter {
	/**
	 * Apply the filter to the given query.
	 * @param Request $request
	 * @param Builder $query
	 * @param mixed $value
	 * @return Builder
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function apply(Request $request, $query, $value): Builder{
		$query->whereHas('variable', function($query) use ($value){
			/** @var Builder $query */
			return $query->where(Variable::TABLE . '.' . Variable::FIELD_VARIABLE_CATEGORY_ID, $value);
		});
		return $query;
	}
	/**
	 * Get the filter's available options.
	 * @param Request $request
	 * @return array
	 */
	public function options(Request $request): array{
		return QMVariableCategory::getOptionsByName();
	}
}
