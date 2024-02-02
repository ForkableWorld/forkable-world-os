<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Astral;
use App\Models\BaseModel;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Slim\Middleware\QMAuth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Http\Requests\AstralRequest;
use Titasgailius\SearchRelations\SearchesRelations;
class UserVariableBaseAstralResource extends BaseAstralAstralResource {
	use SearchesRelations;
	/**
	 * Indicates if the resource should be globally searchable.
	 * @var bool
	 */
	public static $globallySearchable = true;
	/**
	 * The model the resource corresponds to.
	 * @var string
	 */
	public static $model = UserVariable::class;
	/**
	 * The single value that should be used to represent the resource when being displayed.
	 * @var string
	 */
	public static $title = Variable::FIELD_NAME;
	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = []; // Overridden with \App\Astral\UserVariableResource::searchableColumns to exclude id
	public static $with = ['variable'];
	/**
	 * The number of results to display in the global search.
	 * @var int
	 */
	public static $globalSearchResults = 10;
	public static $searchRelations = [
		'variable' => [Variable::FIELD_NAME],
	];
	/**
	 * Determine if relations should be searched globally.
	 * @var array
	 */
	public static $searchRelationsGlobally = true;
	/**
	 * The relationship columns that should be searched globally.
	 * @var array
	 */
	public static $globalSearchRelations = [
		'variable' => [Variable::FIELD_NAME],
	];
	/**
	 * The per-page options used the resource index.
	 * @var array
	 */
	public static $perPageOptions = [10, 25, 50, 100];
	/**
	 * @return UserVariable|BaseModel
	 */
	public function getUserVariable(): UserVariable{
		return $this->getModel();
	}
	/**
	 * @param AstralRequest $request
	 * @param Builder $query
	 * @param array $filters
	 * @return Builder|HasMany
	 */
	public static function applyFilters(AstralRequest $request, $query, array $filters){
		$query = parent::applyFilters($request, $query, $filters);
		$u = QMAuth::getQMUser();
		if(self::isGlobalSearch()){
			$query->where(UserVariable::FIELD_USER_ID, $u->getId());
		}
		return $query;
	}
}
