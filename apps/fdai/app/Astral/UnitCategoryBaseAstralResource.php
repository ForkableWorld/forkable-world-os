<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral;
use App\Models\UnitCategory;
class UnitCategoryBaseAstralResource extends BaseAstralAstralResource {
	/**
	 * The model the resource corresponds to.
	 * @var string
	 */
	public static $model = UnitCategory::class;

	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [
		UnitCategory::FIELD_NAME,
	];
}
