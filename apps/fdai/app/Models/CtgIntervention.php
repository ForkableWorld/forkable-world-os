<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BaseCtgIntervention;
use App\Units\YesNoUnit;
use App\VariableCategories\TreatmentsVariableCategory;
use Illuminate\Database\Eloquent\Builder;
use Titasgailius\SearchRelations\SearchesRelations;
/**
 * App\Models\CtgIntervention
 * @property mixed $raw

 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|CtgIntervention newModelQuery()
 * @method static Builder|CtgIntervention newQuery()
 * @method static Builder|CtgIntervention query()
 * @mixin \Eloquent
 * @property int|null $id
 * @property string|null $nct_id
 * @property string|null $intervention_type
 * @property string|null $name
 * @property string|null $description
 * @property int|null $variable_id
 * @method static Builder|CtgIntervention whereDescription($value)
 * @method static Builder|CtgIntervention whereId($value)
 * @method static Builder|CtgIntervention whereInterventionType($value)
 * @method static Builder|CtgIntervention whereName($value)
 * @method static Builder|CtgIntervention whereNctId($value)
 * @method static Builder|CtgIntervention whereVariableId($value)
 * @property-read Variable|null $variable
 * @property-read OAClient $client
 * @property-read OAClient $oa_client
 */
class CtgIntervention extends BaseCtgIntervention {
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
	public static $search = [//'id',
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
		$v = Variable::findOrCreateByName($this->name, [
			Variable::FIELD_VARIABLE_CATEGORY_ID => TreatmentsVariableCategory::ID,
			Variable::FIELD_DEFAULT_UNIT_ID => YesNoUnit::ID,
			Variable::FIELD_CLIENT_ID => OAClient::clinicalTrialsGov()->c
			//Variable::SOURCE_URL => YesNoUnit::ID,
		]);
		//$v->addSynonymsAndSave($this->intervention_other_names()->);
		return $v;
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
		return "Interventions";
	}
}
