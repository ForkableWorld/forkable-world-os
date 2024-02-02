<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral;
use App\Models\TrackingReminder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Validator;
use App\Http\Requests\AstralRequest;
use Titasgailius\SearchRelations\SearchesRelations;
class TrackingReminderBaseAstralResource extends BaseAstralAstralResource {
	use SearchesRelations;
	/**
	 * The model the resource corresponds to.
	 * @var string
	 */
	public static $model = TrackingReminder::class;

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
		'variable' => ['name'],
	];
	public static $with = [
		'variable',
		'user_variable',
	];
	/**
	 * The per-page options used the resource index.
	 * @var array
	 */
	public static $perPageOptions = [25, 50, 100];
	/**
	 * Handle any post-creation validation processing.
	 * @param AstralRequest $request
	 * @param Validator $validator
	 * @return void
	 */
	protected static function afterCreationValidation(AstralRequest $request, $validator){
		parent::afterCreationValidation($request, $validator);
	}
	/**
	 * Fill a new model instance using the given request.
	 * @param AstralRequest $request
	 * @param Model $model
	 * @return array
	 */
	public static function fill(AstralRequest $request, $model): array{
		return parent::fill($request, $model);
	}
}
