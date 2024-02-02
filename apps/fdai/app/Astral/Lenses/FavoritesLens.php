<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Lenses;
use App\Astral\Actions\FavoriteAction;
use App\Astral\BaseAstralAstralResource;
use App\Slim\Middleware\QMAuth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Http\Requests\LensRequest;
use App\Lenses\Lens;
class FavoritesLens extends Lens {
	/**
	 * @var string
	 */
	private static $label;
	/**
	 * Get the displayable name of the lens.
	 * @return string
	 */
	public function name(): string{
		if(static::$label){
			return static::$label;
		}
		return "Favorites";
	}
	/**
	 * Get the query builder / paginator for the lens.
	 * @param LensRequest $request
	 * @param Builder $query
	 * @return mixed
	 */
	public static function query(LensRequest $request, $query){
		/** @var BaseAstralAstralResource $resource */
		$resource = $request->resource();
		static::$label = "Favorite " . $resource::label();
		return $request->withOrdering($request->withFilters($query->whereHas('favoriters', function($q){
			$user = QMAuth::getQMUser()->l();
			return $q->where(config('favorite.user_foreign_key'), $user->getKey());
		})));
	}
	/**
	 * Get the fields available to the lens.
	 * @param Request|LensRequest $request
	 * @return array
	 */
	public function fields(Request $request): array{
		/** @var BaseAstralAstralResource $resource */
		$resource = $request->resource();
		$fields = $resource::getFields($request);
		return $fields;
	}
	/**
	 * Get the cards available on the lens.
	 * @param Request $request
	 * @return array
	 */
	public function cards(Request $request): array{
		return [];
	}
	/**
	 * Get the filters available for the lens.
	 * @param Request $request
	 * @return array
	 */
	public function filters(Request $request): array{
		return [];
	}
	/**
	 * Get the actions available on the lens.
	 * @param Request $request
	 * @return array
	 */
	public function actions(Request $request): array{
		$all = parent::actions($request);
		$keep = [];
		foreach($all as $one){
			if($one instanceof FavoriteAction){
				continue;
			}
			$keep[] = $one;
		}
		return $keep;
	}
	/**
	 * Get the URI key for the lens.
	 * @return string
	 */
	public function uriKey(): string{
		return 'favorites-lens';
	}
}
