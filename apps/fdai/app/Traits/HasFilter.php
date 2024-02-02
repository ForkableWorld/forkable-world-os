<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Astral\Filters\PropertyFilter;
use App\Properties\BaseProperty;
use Illuminate\Database\Eloquent\Builder;
use App\Filters\Filter;
use App\Http\Requests\AstralRequest;
trait HasFilter {
	public function getFilter(): Filter{
		/** @var BaseProperty $this */
		return new PropertyFilter($this);
	}
	abstract public function getFilterOptions(): array;
	/**
	 * Apply the filter to the given query.
	 * @param Builder $query
	 * @param mixed $type
	 * @return Builder
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	abstract public function applyFilter($query, $type);
	/**
	 * @return bool
	 */
	public function shouldShowFilter(): bool{
		return AstralRequest::shouldShowAnyFilters();
	}
	/**
	 * Set the default options for the filter.
	 * @return string
	 */
	public function defaultFilter(): string{
		return '';
	}
}
