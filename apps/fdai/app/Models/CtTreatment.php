<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BaseCtTreatment;
use App\Types\QMStr;
use App\Units\YesNoUnit;
use App\VariableCategories\TreatmentsVariableCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
/**
 * App\Models\CtTreatment
 * @property int $id
 * @property string $name
 * @property int $variable_id
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon|null $deleted_at
 * @property-read Collection|CtTreatmentSideEffect[]
 *     $ct_treatment_side_effects
 * @property-read int|null $ct_treatment_side_effects_count
 * @property mixed $raw

 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|CtTreatment newModelQuery()
 * @method static Builder|CtTreatment newQuery()
 * @method static Builder|CtTreatment query()
 * @method static Builder|CtTreatment whereCreatedAt($value)
 * @method static Builder|CtTreatment whereDeletedAt($value)
 * @method static Builder|CtTreatment whereId($value)
 * @method static Builder|CtTreatment whereName($value)
 * @method static Builder|CtTreatment whereUpdatedAt($value)
 * @method static Builder|CtTreatment whereVariableId($value)
 * @mixin \Eloquent
 * @property int|null $number_of_conditions
 * @property int $number_of_side_effects
 * @property-read Collection|CtConditionTreatment[] $ct_condition_treatment
 * @property-read int|null $ct_condition_treatment_count
 * @method static Builder|CtTreatment whereNumberOfSideEffects($value)
 * @method static Builder|CtTreatment whereNumberOfSymptoms($value)
 * @method static Builder|CtTreatment whereNumberOfConditions($value)
 * @property-read OAClient $client
 * @property-read Collection|CtConditionTreatment[] $ct_condition_treatments
 * @property-read int|null $ct_condition_treatments_count
 * @property-read OAClient $oa_client
 * @property-read Variable $variable
 */
class CtTreatment extends BaseCtTreatment {
	const CLASS_CATEGORY = Variable::CLASS_CATEGORY;
    //protected $connection= ClinicalTrialsDB::CONNECTION_NAME;

	public function getVariable(): Variable{
		$name = QMStr::titleCaseSlow($this->name);
		if($name === "K"){
			$name = "Ketamine";
		}
		return Variable::findOrCreateByName($this->name, [
			Variable::FIELD_VARIABLE_CATEGORY_ID => TreatmentsVariableCategory::ID,
			Variable::FIELD_DEFAULT_UNIT_ID => YesNoUnit::ID,
		]);
	}
	public static function findVariable(int $id): Variable{
		$me = static::findInMemoryOrDB($id);
		return $me->getVariable();
	}
	public function ct_condition_treatment(): HasMany{
		return $this->hasMany(CtConditionTreatment::class, CtConditionTreatment::FIELD_TREATMENT_ID,
			CtTreatmentSideEffect::FIELD_ID);
	}
}
