<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral;
use App\Models\User;
class UserBaseAstralResource extends BaseAstralAstralResource {
	/**
	 * The model the resource corresponds to.
	 * @var string
	 */
	public static $model = User::class;
	/**
	 * The single value that should be used to represent the resource when being displayed.
	 * @var string
	 */
	public static $title = User::FIELD_DISPLAY_NAME;
	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [
		User::FIELD_ID,
		User::FIELD_DISPLAY_NAME,
		User::FIELD_USER_EMAIL,
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
}
