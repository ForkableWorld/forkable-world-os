<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Logging\QMLog;
use App\Models\Base\BaseCtConditionCause;
use App\Storage\DB\Writable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
/**
 * App\Models\CtConditionCause
 * @property int $condition_id
 * @property int $cause_id
 * @property int $votes_percent
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon|null $deleted_at
 * @property int $condition_variable_id
 * @property int $cause_variable_id
 * @property mixed $raw

 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|CtConditionCause newModelQuery()
 * @method static Builder|CtConditionCause newQuery()
 * @method static Builder|CtConditionCause query()
 * @method static Builder|CtConditionCause whereCauseId($value)
 * @method static Builder|CtConditionCause whereCauseVariableId($value)
 * @method static Builder|CtConditionCause whereConditionId($value)
 * @method static Builder|CtConditionCause whereConditionVariableId($value)
 * @method static Builder|CtConditionCause whereCreatedAt($value)
 * @method static Builder|CtConditionCause whereDeletedAt($value)
 * @method static Builder|CtConditionCause whereUpdatedAt($value)
 * @method static Builder|CtConditionCause whereVotesPercent($value)
 * @mixin \Eloquent
 * @property int $id
 * @property-read Variable $cause_variable
 * @property-read Variable $condition_variable
 * @method static Builder|CtConditionCause whereId($value)
 * @property-read OAClient $client
 * @property-read OAClient $oa_client
 */
class CtConditionCause extends BaseCtConditionCause {
	const CLASS_CATEGORY = Study::CLASS_CATEGORY;

	public static function populateTable(){
		$qb = Writable::db()->table(static::TABLE . "_bak");
		$rows = $qb->get();
		/** @var static $row */
		foreach($rows as $row){
			$exists =
				self::where(self::FIELD_CONDITION_ID, $row->condition_id)->where(self::FIELD_CAUSE_ID, $row->cause_id)
					->first();
			if($exists){
				$exist[] = $row;
				continue;
			}
			unset($row->id);
			$row->condition_variable_id = CtCondition::findVariable($row->condition_id)->id;
			$row->cause_variable_id = CtCause::findVariable($row->cause_id)->id;
			try {
				self::insert((array)$row);
			} catch (QueryException $e) {
				QMLog::info(__METHOD__.": ".$e->getMessage());
			}
		}
	}
}
