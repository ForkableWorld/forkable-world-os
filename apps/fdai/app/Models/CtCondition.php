<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Buttons\QMButton;
use App\Models\Base\BaseCtCondition;
use App\Units\YesNoUnit;
use App\VariableCategories\ConditionsVariableCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
/**
 * App\Models\CtCondition
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
 * @method static Builder|CtCondition newModelQuery()
 * @method static Builder|CtCondition newQuery()
 * @method static Builder|CtCondition query()
 * @method static Builder|CtCondition whereCreatedAt($value)
 * @method static Builder|CtCondition whereDeletedAt($value)
 * @method static Builder|CtCondition whereId($value)
 * @method static Builder|CtCondition whereName($value)
 * @method static Builder|CtCondition whereUpdatedAt($value)
 * @method static Builder|CtCondition whereVariableId($value)
 * @mixin \Eloquent
 * @property int $number_of_treatments
 * @property int|null $number_of_symptoms
 * @property int $number_of_causes
 * @method static Builder|CtCondition whereNumberOfCauses($value)
 * @method static Builder|CtCondition whereNumberOfSymptoms($value)
 * @method static Builder|CtCondition whereNumberOfTreatments($value)
 * @property-read OAClient $client
 * @property-read Collection|CtConditionCause[] $intuitive_condition_cause_votes
 * @property-read int|null $intuitive_condition_cause_votes_count
 * @property-read Collection|CtConditionSymptom[] $ct_condition_symptoms
 * @property-read int|null $ct_condition_symptoms_count
 * @property-read Collection|CtConditionTreatment[] $ct_condition_treatments
 * @property-read int|null $ct_condition_treatments_count
 * @property-read OAClient $oa_client
 * @property-read Variable $variable
 */
class CtCondition extends BaseCtCondition {
	const CLASS_CATEGORY = Variable::CLASS_CATEGORY;

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
	public function getButton(): QMButton{
		$v = $this->getVariable();
		$b = $v->getButton();
		$num = $this->number_of_treatments;
		if(!$num){
			le("no number_of_treatments for $v->name");
		}
		$b->setBadgeText($num);
		$b->setTooltip("$num treatment studies");
		return $b;
	}
	public function variable(): BelongsTo{
		return $this->belongsTo(Variable::class, self::FIELD_VARIABLE_ID, Variable::FIELD_ID, self::FIELD_VARIABLE_ID);
	}
	public function intuitive_condition_cause_votes(): HasMany{
		return $this->hasMany(CtConditionCause::class, CtConditionCause::FIELD_CONDITION_ID, static::FIELD_ID);
	}
	public function ct_condition_treatments(): HasMany{
		return $this->hasMany(CtConditionTreatment::class, CtConditionTreatment::FIELD_CONDITION_ID, static::FIELD_ID);
	}
	public function ct_condition_symptoms(): HasMany{
		return $this->hasMany(CtConditionSymptom::class, CtConditionSymptom::FIELD_CONDITION_ID, static::FIELD_ID);
	}
}
