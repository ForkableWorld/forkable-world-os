<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Astral;
use App\Models\UserVariableRelationship;
use App\Models\User;
use App\Models\Variable;
use Titasgailius\SearchRelations\SearchesRelations;
class CorrelationBaseAstralResource extends BaseAstralAstralResource {
	use SearchesRelations;
	/**
	 * The model the resource corresponds to.
	 * @var string
	 */
	public static $model = UserVariableRelationship::class;
	/**
	 * The single value that should be used to represent the resource when being displayed.
	 * @var string
	 */
	public static $title = UserVariableRelationship::FIELD_ID;
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
	public function getCorrelation(): UserVariableRelationship{
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $this->getModel();
	}
}
