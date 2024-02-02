<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\AstralRequest;
class UnitBaseAstralResource extends BaseAstralAstralResource {
	/**
	 * The model the resource corresponds to.
	 * @var string
	 */
	public static $model = Unit::class;
	/**
	 * The single value that should be used to represent the resource when being displayed.
	 * @var string
	 */
	public static $title = Unit::FIELD_NAME;
	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [Unit::FIELD_NAME];
	/**
	 * The relationship columns that should be searched.
	 * @var array
	 */
	public static $searchRelations = [];
	/**
	 * The per-page options used the resource index.
	 * @var array
	 */
	public static $perPageOptions = [100];
	/**
	 * Indicates if the resource should be globally searchable.
	 * @var bool
	 */
	public static $globallySearchable = false;
	/**
	 * Build an "index" query for the given resource.
	 * @param AstralRequest $request
	 * @param Builder $query
	 * @return Builder
	 */
	public static function indexQuery(AstralRequest $request, $query): Builder{
		$query = parent::indexQuery($request, $query);
		//$query->where(Unit::FIELD_ADVANCED, 0);  // Can't hide or we see the wrong units for existing measurements with advanced units
		return $query;
	}
}
