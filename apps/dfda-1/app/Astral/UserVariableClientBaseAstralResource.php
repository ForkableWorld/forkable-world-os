<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral;
use App\Models\User;
use App\Models\UserVariableClient;
use Titasgailius\SearchRelations\SearchesRelations;
class UserVariableClientBaseAstralResource extends BaseAstralAstralResource {
	use SearchesRelations;
	/**
	 * The model the resource corresponds to.
	 * @var string
	 */
	public static $model = UserVariableClient::class;

	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [
		'id',
	];
	public static $with = ['user'];
	/**
	 * The relationship columns that should be searched.
	 * @var array
	 */
	public static $searchRelations = [
		'user' => [User::FIELD_DISPLAY_NAME],
	];
}