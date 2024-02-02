<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Logging\QMLog;
use App\Models\Base\BaseCtConditionSymptom;
use App\Storage\DB\Writable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
/**
 * App\Models\CtConditionSymptom
 * @property int $condition_id
 * @property int $symptom_id
 * @property int $votes
 * @property int|null $extreme
 * @property int|null $severe
 * @property int|null $moderate
 * @property int|null $mild
 * @property int|null $minimal
 * @property int|null $no_symptoms
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon|null $deleted_at
 * @property int $condition_variable_id
 * @property int $symptom_variable_id
 * @property mixed $raw

 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|CtConditionSymptom newModelQuery()
 * @method static Builder|CtConditionSymptom newQuery()
 * @method static Builder|CtConditionSymptom query()
 * @method static Builder|CtConditionSymptom whereConditionId($value)
 * @method static Builder|CtConditionSymptom whereConditionVariableId($value)
 * @method static Builder|CtConditionSymptom whereCreatedAt($value)
 * @method static Builder|CtConditionSymptom whereDeletedAt($value)
 * @method static Builder|CtConditionSymptom whereExtreme($value)
 * @method static Builder|CtConditionSymptom whereMild($value)
 * @method static Builder|CtConditionSymptom whereMinimal($value)
 * @method static Builder|CtConditionSymptom whereModerate($value)
 * @method static Builder|CtConditionSymptom whereNoSymptoms($value)
 * @method static Builder|CtConditionSymptom whereSevere($value)
 * @method static Builder|CtConditionSymptom whereSymptomId($value)
 * @method static Builder|CtConditionSymptom whereSymptomVariableId($value)
 * @method static Builder|CtConditionSymptom whereUpdatedAt($value)
 * @method static Builder|CtConditionSymptom whereVotes($value)
 * @mixin \Eloquent
 * @property int $id
 * @property-read Variable $condition_variable
 * @property-read Variable $symptom_variable
 * @method static Builder|CtConditionSymptom whereId($value)
 * @property-read OAClient $client
 * @property-read OAClient $oa_client
 */
class CtConditionSymptom extends BaseCtConditionSymptom {
	const CLASS_CATEGORY = Study::CLASS_CATEGORY;

	public static function populateTable(){
		$qb = Writable::db()->table(static::TABLE . "_bak");
		$rows = $qb->get();
		/** @var static $row */
		foreach($rows as $row){
			$exists = self::where(self::FIELD_CONDITION_ID, $row->condition_id)
				->where(self::FIELD_SYMPTOM_ID, $row->symptom_id)->first();
			if($exists){
				$exist[] = $row;
				continue;
			}
			unset($row->id);
			$row->condition_variable_id = CtCondition::findVariable($row->condition_id)->id;
			$row->symptom_variable_id = CtSymptom::findVariable($row->symptom_id)->id;
			try {
				self::insert((array)$row);
			} catch (QueryException $e) {
				QMLog::info(__METHOD__.": ".$e->getMessage());
			}
		}
	}
}
