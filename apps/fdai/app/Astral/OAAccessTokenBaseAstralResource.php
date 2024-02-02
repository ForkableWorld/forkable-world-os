<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral;
use App\Models\OAAccessToken;
use App\Models\User;
use Titasgailius\SearchRelations\SearchesRelations;
class OAAccessTokenBaseAstralResource extends BaseAstralAstralResource {
	use SearchesRelations;
	/**
	 * The model the resource corresponds to.
	 * @var string
	 */
	public static $model = OAAccessToken::class;
	/**
	 * The single value that should be used to represent the resource when being displayed.
	 * @var string
	 */
	public static $title = OAAccessToken::FIELD_ID;
	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [
		OAAccessToken::FIELD_CLIENT_ID,
		OAAccessToken::FIELD_ACCESS_TOKEN,
	];
	/**
	 * The relationship columns that should be searched.
	 * @var array
	 */
	public static $searchRelations = [
		'user' => [User::FIELD_DISPLAY_NAME],
	];
	public static $with = ['user'];
}
