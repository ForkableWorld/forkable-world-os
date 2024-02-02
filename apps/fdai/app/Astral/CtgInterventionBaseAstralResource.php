<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral;
use App\Models\CtgIntervention;
use App\Models\Variable;
use Illuminate\Http\Request;
use Titasgailius\SearchRelations\SearchesRelations;
class CtgInterventionBaseAstralResource extends BaseAstralAstralResource {
	use SearchesRelations;
	/**
	 * The model the resource corresponds to.
	 * @var string
	 */
	public static $model = CtgIntervention::class;

	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [//'id',
	];
	/**
	 * The relationship columns that should be searched.
	 * @var array
	 */
	public static $searchRelations = [
		'variable' => [Variable::FIELD_NAME],
	];
	public static $with = ['variable'];
	/**
	 * Determine if this resource is available for navigation.
	 * @param Request $request
	 * @return bool
	 */
	public static function availableForNavigation(Request $request): bool{ return true; }
}
