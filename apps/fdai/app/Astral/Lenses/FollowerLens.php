<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Lenses;
use App\Slim\Middleware\QMAuth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Fields\ID;
use App\Http\Requests\LensRequest;
use App\Lenses\Lens;
class FollowerLens extends Lens {
	/**
	 * Get the query builder / paginator for the lens.
	 * @param LensRequest $request
	 * @param Builder $query
	 * @return mixed
	 */
	public static function query(LensRequest $request, $query){
		$user = QMAuth::getQMUser()->l();
		return $request->withOrdering($request->withFilters($user->followers()->getQuery()));
	}
	/**
	 * Get the fields available to the lens.
	 * @param Request $request
	 * @return array
	 */
	public function fields(Request $request){
		return [
			ID::make('ID', 'id')->sortable(),
		];
	}
	/**
	 * Get the cards available on the lens.
	 * @param Request $request
	 * @return array
	 */
	public function cards(Request $request): array{
		return parent::cards($request);
	}
	/**
	 * Get the filters available for the lens.
	 * @param Request $request
	 * @return array
	 */
	public function filters(Request $request){
		return [];
	}
	/**
	 * Get the actions available on the lens.
	 * @param Request $request
	 * @return array
	 */
	public function actions(Request $request){
		return parent::actions($request);
	}
	/**
	 * Get the URI key for the lens.
	 * @return string
	 */
	public function uriKey(): string {
		return 'follower-lens';
	}
}
