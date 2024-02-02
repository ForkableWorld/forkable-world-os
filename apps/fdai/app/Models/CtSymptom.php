<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BaseCtSymptom;
use App\Units\OneToFiveRatingUnit;
use App\VariableCategories\SymptomsVariableCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
/**
 * App\Models\CtSymptom
 * @property int $id
 * @property string $name
 * @property int $variable_id
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon|null $deleted_at
 * @property mixed $raw

 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|CtSymptom newModelQuery()
 * @method static Builder|CtSymptom newQuery()
 * @method static Builder|CtSymptom query()
 * @method static Builder|CtSymptom whereCreatedAt($value)
 * @method static Builder|CtSymptom whereDeletedAt($value)
 * @method static Builder|CtSymptom whereId($value)
 * @method static Builder|CtSymptom whereName($value)
 * @method static Builder|CtSymptom whereUpdatedAt($value)
 * @method static Builder|CtSymptom whereVariableId($value)
 * @mixin \Eloquent
 * @property int $number_of_conditions
 * @method static Builder|CtSymptom whereNumberOfConditions($value)
 * @property-read OAClient $client
 * @property-read Collection|CtConditionSymptom[] $ct_condition_symptoms
 * @property-read int|null $ct_condition_symptoms_count
 * @property-read OAClient $oa_client
 * @property-read Variable $variable
 */
class CtSymptom extends BaseCtSymptom {
	const CLASS_CATEGORY = Variable::CLASS_CATEGORY;
    //protected $connection= ClinicalTrialsDB::CONNECTION_NAME;

	public function getVariable(): Variable{
		return Variable::findOrCreateByName($this->name, [
			Variable::FIELD_VARIABLE_CATEGORY_ID => SymptomsVariableCategory::ID,
			Variable::FIELD_DEFAULT_UNIT_ID => OneToFiveRatingUnit::ID,
		]);
	}
	public static function findVariable(int $id): Variable{
		$me = static::findInMemoryOrDB($id);
		return $me->getVariable();
	}
	public function ct_condition_symptoms(): HasMany{
		return $this->hasMany(CtConditionSymptom::class, CtConditionSymptom::FIELD_SYMPTOM_ID, static::FIELD_ID);
	}
}
