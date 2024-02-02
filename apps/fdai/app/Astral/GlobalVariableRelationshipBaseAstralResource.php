<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral;
use App\Models\GlobalVariableRelationship;
use Titasgailius\SearchRelations\SearchesRelations;
class GlobalVariableRelationshipBaseAstralResource extends BaseAstralAstralResource {
	use SearchesRelations;
	/**
	 * The model the resource corresponds to.
	 * @var string
	 */
	public static $model = GlobalVariableRelationship::class;

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
		'cause_variable' => ['name'],
		'effect_variable' => ['name'],
	];
	/**
	 * The relationships that should be eager loaded on index queries.
	 * @var array
	 */
	public static $with = ['cause_variable', 'effect_variable'];
}
