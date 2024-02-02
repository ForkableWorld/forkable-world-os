<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Logging\QMLog;
use App\Models\Base\BaseCtConditionTreatment;
use App\Storage\DB\Writable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
/**
 * App\Models\CtConditionTreatment
 * @property int $condition_id
 * @property int $treatment_id
 * @property int $major_improvement
 * @property int $moderate_improvement
 * @property int $no_effect
 * @property int $worse
 * @property int $much_worse
 * @property int $popularity
 * @property int $average_effect
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon|null $deleted_at
 * @property int|null $condition_variable_id
 * @property int $treatment_variable_id
 * @property mixed $raw

 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|CtConditionTreatment newModelQuery()
 * @method static Builder|CtConditionTreatment newQuery()
 * @method static Builder|CtConditionTreatment query()
 * @method static Builder|CtConditionTreatment whereAverageEffect($value)
 * @method static Builder|CtConditionTreatment whereConditionId($value)
 * @method static Builder|CtConditionTreatment whereConditionVariableId($value)
 * @method static Builder|CtConditionTreatment whereCreatedAt($value)
 * @method static Builder|CtConditionTreatment whereDeletedAt($value)
 * @method static Builder|CtConditionTreatment whereMajorImprovement($value)
 * @method static Builder|CtConditionTreatment whereModerateImprovement($value)
 * @method static Builder|CtConditionTreatment whereMuchWorse($value)
 * @method static Builder|CtConditionTreatment whereNoEffect($value)
 * @method static Builder|CtConditionTreatment wherePopularity($value)
 * @method static Builder|CtConditionTreatment whereTreatmentId($value)
 * @method static Builder|CtConditionTreatment whereTreatmentVariableId($value)
 * @method static Builder|CtConditionTreatment whereUpdatedAt($value)
 * @method static Builder|CtConditionTreatment whereWorse($value)
 * @mixin \Eloquent
 * @property int $id
 * @property-read Variable|null $condition_variable
 * @property-read Variable $treatment_variable
 * @method static Builder|CtConditionTreatment whereId($value)
 * @property-read OAClient $client
 * @property-read OAClient $oa_client
 */
class CtConditionTreatment extends BaseCtConditionTreatment {
	const CLASS_CATEGORY = Study::CLASS_CATEGORY;

	public static function populateTable(){
		$qb = Writable::db()->table(static::TABLE . "_bak");
		$rows = $qb->get();
		/** @var static $row */
		foreach($rows as $row){
			$exists = self::where(self::FIELD_CONDITION_ID, $row->condition_id)
				->where(self::FIELD_TREATMENT_ID, $row->treatment_id)->first();
			if($exists){
				$exist[] = $row;
				continue;
			}
			unset($row->id);
			$row->condition_variable_id = CtCondition::findVariable($row->condition_id)->id;
			$row->treatment_variable_id = CtTreatment::findVariable($row->treatment_id)->id;
			try {
				self::insert((array)$row);
			} catch (QueryException $e) {
				QMLog::info(__METHOD__.": ".$e->getMessage());
			}
		}
	}
}
