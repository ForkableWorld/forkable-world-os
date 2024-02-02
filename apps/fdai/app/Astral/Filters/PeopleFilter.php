<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Filters;
use App\Properties\User\UserIdProperty;
use App\Slim\Middleware\QMAuth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Filters\Filter;
class PeopleFilter extends Filter {
	const EVERYONE = 'everyone';
	const YOURS = 'yours';
	/**
	 * The filter's component.
	 * @var string
	 */
	public $component = 'select-filter';
	/**
	 * Apply the filter to the given query.
	 * @param Request $request
	 * @param Builder $query
	 * @param mixed $value
	 * @return Builder
	 */
	public function apply(Request $request, $query, $value): Builder{
		if($id = UserIdProperty::fromReferrer()){
			$query->where('user_id', $id);
		} elseif($value === self::YOURS){
			$query->where('user_id', QMAuth::id());
		}
		return $query;
	}
	/**
	 * Set the default options for the filter.
	 * @return array
	 */
	public function default(): array{
		return [
			'unused',
		];
	}
	/**
	 * Get the filter's available options.
	 * @param Request $request
	 * @return array
	 */
	public function options(Request $request): array{
		return [
			'Yours' => self::YOURS,
			'Everyone' => self::EVERYONE,
		];
	}
}
