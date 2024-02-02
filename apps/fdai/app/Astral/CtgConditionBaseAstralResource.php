<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral;
use App\Models\CtgCondition;
use App\Models\Variable;
use Titasgailius\SearchRelations\SearchesRelations;
class CtgConditionBaseAstralResource extends BaseAstralAstralResource {
	use SearchesRelations;
	/**
	 * The model the resource corresponds to.
	 * @var string
	 */
	public static $model = CtgCondition::class;

	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [
		'id',
	];
	/**
	 * The relationship columns that should be searched.
	 * @var array
	 */
	public static $searchRelations = [
		'variable' => [Variable::FIELD_NAME],
	];
	public static $with = ['variable'];
}
