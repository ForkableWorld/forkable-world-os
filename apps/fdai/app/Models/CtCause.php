<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BaseCtCause;
use App\Units\YesNoUnit;
use App\VariableCategories\CausesOfIllnessVariableCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;
/**
 * App\Models\CtCause
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
 * @method static Builder|CtCause newModelQuery()
 * @method static Builder|CtCause newQuery()
 * @method static Builder|CtCause query()
 * @method static Builder|CtCause whereCreatedAt($value)
 * @method static Builder|CtCause whereDeletedAt($value)
 * @method static Builder|CtCause whereId($value)
 * @method static Builder|CtCause whereName($value)
 * @method static Builder|CtCause whereUpdatedAt($value)
 * @method static Builder|CtCause whereVariableId($value)
 * @mixin \Eloquent
 * @property int $number_of_conditions
 * @method static Builder|CtCause whereNumberOfConditions($value)
 * @property-read OAClient|null $client
 * @property-read Collection|CtCondition[] $conditions
 * @property-read int|null $conditions_count
 * @property-read Collection|CtConditionCause[] $intuitive_condition_cause_votes
 * @property-read int|null $intuitive_condition_cause_votes_count
 * @property-read OAClient|null $oa_client
 * @property-read Variable $variable
 */
class CtCause extends BaseCtCause {
	const CLASS_CATEGORY = "Studies";

	public function getVariable(): Variable{
		return Variable::findOrCreateByName($this->name, [
			Variable::FIELD_VARIABLE_CATEGORY_ID => CausesOfIllnessVariableCategory::ID,
			Variable::FIELD_DEFAULT_UNIT_ID => YesNoUnit::ID,
		]);
	}
	public static function findVariable(int $id): Variable{
		$me = static::findInMemoryOrDB($id);
		return $me->getVariable();
	}
	/**
	 * @return HasManyThrough|CtCondition[]
	 */
	public function conditions(): HasManyThrough{
		//return $this->hasManyThrough('intuitive_condition_cause_votes');
		return $this->hasManyThrough(CtCondition::class, CtConditionCause::class, CtConditionCause::FIELD_CAUSE_ID,
			// Foreign key on users table...
			'id', // Foreign key on posts table...
			'id', // Local key on countries table...
			'id' // Local key on users table...
		);
	}
}
