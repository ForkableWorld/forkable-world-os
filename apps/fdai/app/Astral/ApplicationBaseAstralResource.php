<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral;
use App\Models\Application;
class ApplicationBaseAstralResource extends BaseAstralAstralResource {
	/**
	 * The model the resource corresponds to.
	 * @var string
	 */
	public static $model = Application::class;
	/**
	 * The single value that should be used to represent the resource when being displayed.
	 * @var string
	 */
	public static $title = Application::FIELD_APP_DISPLAY_NAME;
	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [
		Application::FIELD_APP_DISPLAY_NAME,
	];
}
