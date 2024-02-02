<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Logging\QMLog;
use App\Models\Base\BaseCtTreatmentSideEffect;
use App\Storage\DB\Writable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
/**
 * App\Models\CtTreatmentSideEffect
 * @property int $id
 * @property int $treatment_variable_id
 * @property int $side_effect_variable_id
 * @property int $treatment_id
 * @property int $side_effect_id
 * @property int $votes_percent
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon|null $deleted_at
 * @property-read CtSideEffect $ct_side_effect
 * @property mixed $raw

 * @property-read Variable $side_effect_variable
 * @property-read Collection|CtTreatmentSideEffect[] $side_effects
 * @property-read int|null $side_effects_count
 * @property-read Variable $treatment_variable
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|CtTreatmentSideEffect newModelQuery()
 * @method static Builder|CtTreatmentSideEffect newQuery()
 * @method static Builder|CtTreatmentSideEffect query()
 * @method static Builder|CtTreatmentSideEffect whereCreatedAt($value)
 * @method static Builder|CtTreatmentSideEffect whereDeletedAt($value)
 * @method static Builder|CtTreatmentSideEffect whereId($value)
 * @method static Builder|CtTreatmentSideEffect whereSideEffectId($value)
 * @method static Builder|CtTreatmentSideEffect whereSideEffectVariableId($value)
 * @method static Builder|CtTreatmentSideEffect whereTreatmentId($value)
 * @method static Builder|CtTreatmentSideEffect whereTreatmentVariableId($value)
 * @method static Builder|CtTreatmentSideEffect whereUpdatedAt($value)
 * @method static Builder|CtTreatmentSideEffect whereVotesPercent($value)
 * @mixin \Eloquent
 * @property-read OAClient $client
 * @property-read OAClient $oa_client
 */
class CtTreatmentSideEffect extends BaseCtTreatmentSideEffect {
	const CLASS_CATEGORY = Study::CLASS_CATEGORY;
    //protected $connection= ClinicalTrialsDB::CONNECTION_NAME;

	public function side_effects(): HasMany{
		return $this->hasMany(CtTreatmentSideEffect::class, CtTreatmentSideEffect::FIELD_TREATMENT_VARIABLE_ID,
			Variable::FIELD_ID);
	}
	public static function populateTable(){
		$qb = Writable::db()->table("ct_treatment_side_effect3");
		$rows = $qb->get();
		/** @var CtTreatmentSideEffect $row */
		foreach($rows as $row){
			$exists = CtTreatmentSideEffect::where(CtTreatmentSideEffect::FIELD_TREATMENT_ID, $row->treatment_id)
				->where(CtTreatmentSideEffect::FIELD_SIDE_EFFECT_ID, $row->side_effect_id)->first();
			if($exists){
				$exist[] = $row;
				continue;
			}
			unset($row->id);
			$row->treatment_variable_id = CtTreatment::findVariable($row->treatment_id)->id;
			$row->side_effect_variable_id = CtSideEffect::findVariable($row->side_effect_id)->id;
			try {
				CtTreatmentSideEffect::insert((array)$row);
			} catch (QueryException $e) {
				QMLog::info(__METHOD__.": ".$e->getMessage());
			}
		}
	}
}
