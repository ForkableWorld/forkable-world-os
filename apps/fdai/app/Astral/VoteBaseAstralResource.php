<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral;
use App\Models\User;
use App\Models\Variable;
use App\Models\Vote;
use Illuminate\Http\Request;
use Titasgailius\SearchRelations\SearchesRelations;
class VoteBaseAstralResource extends BaseAstralAstralResource {
	use SearchesRelations;
	/**
	 * The model the resource corresponds to.
	 * @var string
	 */
	public static $model = Vote::class;

	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [];
	/**
	 * The relationship columns that should be searched.
	 * @var array
	 */
	public static $searchRelations = [
		'cause_variable' => [Variable::FIELD_NAME],
		'effect_variable' => [Variable::FIELD_NAME],
		'user' => [User::FIELD_DISPLAY_NAME],
	];
	/**
	 * Indicates if the resource should be globally searchable.
	 * @var bool
	 */
	public static $globallySearchable = false;
	/**
	 * The per-page options used the resource index.
	 * @var array
	 */
	public static $perPageOptions = [10, 25, 50, 100];
	/**
	 * The relationships that should be eager loaded on index queries.
	 * @var array
	 */
	public static $with = ['cause_variable', 'effect_variable', 'user'];
	/**
	 * Determine if this resource is available for navigation.
	 * @param Request $request
	 * @return bool
	 */
	public static function availableForNavigation(Request $request): bool{
		return true;
		// Sometimes user is out of date?  return (bool)QMAuth::getUser()->getNumberOfCorrelations();
	}
}
