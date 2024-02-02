<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Filters;
use App\Properties\BaseProperty;
use App\Traits\HasFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use App\Filters\Filter;
class PropertyFilter extends Filter {
	/**
	 * The column that should be filtered on.
	 * @var string
	 */
	protected $property;
	/**
	 * Create a new filter instance.
	 * @param BaseProperty $property
	 */
	public function __construct(BaseProperty $property){
		$this->property = $property;
	}
	/**
	 * Apply the filter to the given query.
	 * @param Request $request
	 * @param Builder $query
	 * @param mixed $value
	 * @return Builder|HasMany
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function apply(Request $request, $query, $value){
		$property = $this->getProperty();
		$property->applyFilter($query, $value);
		return $query;
	}
	/**
	 * Get the key for the filter.
	 * @return string
	 */
	public function key(): string{
		// TODO: Update \Tests\StagingUnitTests\D\AppConnectionsTest
		// so it doesn't require invalid_ prefix and remove this
		return 'invalid_' . $this->getProperty()->name;
	}
	/**
	 * Get the filter's available options.
	 * @param Request $request
	 * @return array
	 */
	public function options(Request $request): array{
		$prop = $this->getProperty();
		if(!method_exists($prop, 'getFilter')){
			le("!method_exists(prop, 'getFilter'))");
		}
		/** @var HasFilter $prop */
		return $prop->getFilterOptions();
	}
	public function default(){
		$prop = $this->getProperty();
		if(!method_exists($prop, 'getFilter')){
			le("!method_exists(prop, 'getFilter'))");
		}
		/** @var HasFilter $prop */
		return $prop->defaultFilter();
	}
	/**
	 * @return BaseProperty|HasFilter
	 */
	public function getProperty(): BaseProperty{
		return $this->property;
	}
	public function name(): string{
		$title = $this->getProperty()->getTitleAttribute();
		return $title;
	}
}
