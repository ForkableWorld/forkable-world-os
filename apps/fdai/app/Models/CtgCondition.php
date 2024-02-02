<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BaseCtgCondition;
use App\Units\YesNoUnit;
use App\VariableCategories\ConditionsVariableCategory;
use Illuminate\Database\Eloquent\Builder;
use Titasgailius\SearchRelations\SearchesRelations;
/**
 * App\Models\CtgCondition
 * @property mixed $raw

 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|CtgCondition newModelQuery()
 * @method static Builder|CtgCondition newQuery()
 * @method static Builder|CtgCondition query()
 * @mixin \Eloquent
 * @property int|null $id
 * @property string|null $nct_id
 * @property string|null $name
 * @property string|null $downcase_name
 * @property int|null $variable_id
 * @method static Builder|CtgCondition whereDowncaseName($value)
 * @method static Builder|CtgCondition whereId($value)
 * @method static Builder|CtgCondition whereName($value)
 * @method static Builder|CtgCondition whereNctId($value)
 * @method static Builder|CtgCondition whereVariableId($value)
 * @property-read Variable|null $variable
 * @property-read OAClient $client
 * @property-read OAClient $oa_client
 */
class CtgCondition extends BaseCtgCondition {
	use SearchesRelations;
    //protected $connection= ClinicalTrialsDB::CONNECTION_NAME;
	/**
	 * The single value that should be used to represent the resource when being displayed.
	 * @var string
	 */
	public static $title = 'id';
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
	public $with = ['variable'];
	public static $group = Variable::CLASS_CATEGORY;

	public function getVariable(): Variable{
		return Variable::findOrCreateByName($this->name, [
			Variable::FIELD_VARIABLE_CATEGORY_ID => ConditionsVariableCategory::ID,
			Variable::FIELD_DEFAULT_UNIT_ID => YesNoUnit::ID,
		]);
	}
	public static function findVariable(int $id): Variable{
		$me = static::findInMemoryOrDB($id);
		return $me->getVariable();
	}
	/**
	 * Get the displayable label of the resource.
	 * @return string
	 */
	public static function label(): string{
		return "Conditions";
	}
}
