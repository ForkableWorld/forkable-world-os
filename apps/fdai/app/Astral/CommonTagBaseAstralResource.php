<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral;
use App\Models\CommonTag;
use Titasgailius\SearchRelations\SearchesRelations;
class CommonTagBaseAstralResource extends BaseAstralAstralResource {
	use SearchesRelations;
	/**
	 * The model the resource corresponds to.
	 * @var string
	 */
	public static $model = CommonTag::class;

	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [
		'id',
	];
}
