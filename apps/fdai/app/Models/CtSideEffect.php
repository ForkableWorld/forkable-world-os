<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BaseCtSideEffect;
use App\Types\QMStr;
use App\Units\OneToFiveRatingUnit;
use App\VariableCategories\SymptomsVariableCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
/**
 * App\Models\CtSideEffect
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
 * @method static Builder|CtSideEffect newModelQuery()
 * @method static Builder|CtSideEffect newQuery()
 * @method static Builder|CtSideEffect query()
 * @method static Builder|CtSideEffect whereCreatedAt($value)
 * @method static Builder|CtSideEffect whereDeletedAt($value)
 * @method static Builder|CtSideEffect whereId($value)
 * @method static Builder|CtSideEffect whereName($value)
 * @method static Builder|CtSideEffect whereUpdatedAt($value)
 * @method static Builder|CtSideEffect whereVariableId($value)
 * @mixin \Eloquent
 * @property int $number_of_treatments
 * @method static Builder|CtSideEffect whereNumberOfTreatments($value)
 * @property-read Variable $variable
 * @property-read OAClient $client
 * @property-read OAClient $oa_client
 */
class CtSideEffect extends BaseCtSideEffect {
	const CLASS_CATEGORY = Variable::CLASS_CATEGORY;
    //protected $connection= ClinicalTrialsDB::CONNECTION_NAME;

	public function getVariable(): Variable{
		$name = QMStr::titleCaseSlow($this->name);
		return Variable::findOrCreateByName($name, [
			Variable::FIELD_VARIABLE_CATEGORY_ID => SymptomsVariableCategory::ID,
			Variable::FIELD_DEFAULT_UNIT_ID => OneToFiveRatingUnit::ID,
		]);
	}
	public static function findVariable(int $id): Variable{
		$me = static::findInMemoryOrDB($id);
		return $me->getVariable();
	}
	public function ct_treatment_side_effects(): HasMany{
		return $this->hasMany(CtTreatmentSideEffect::class, CtTreatmentSideEffect::FIELD_SIDE_EFFECT_ID,
			static::FIELD_ID);
	}
}
