<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BaseTrackerLog;
use App\Traits\HasModel\HasUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * App\Models\TrackerLog
 * @property int $id
 * @property int|null $session_id
 * @property int|null $path_id
 * @property int|null $query_id
 * @property string $method
 * @property int|null $route_path_id
 * @property int $is_ajax
 * @property int $is_secure
 * @property int $is_json
 * @property int $wants_json
 * @property int|null $error_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $client_id
 * @property int|null $user_id
 * @property string|null $deleted_at
 * @method static Builder|TrackerLog newModelQuery()
 * @method static Builder|TrackerLog newQuery()
 * @method static Builder|TrackerLog query()
 * @method static Builder|TrackerLog whereClientId($value)
 * @method static Builder|TrackerLog whereCreatedAt($value)
 * @method static Builder|TrackerLog whereDeletedAt($value)
 * @method static Builder|TrackerLog whereErrorId($value)
 * @method static Builder|TrackerLog whereId($value)
 * @method static Builder|TrackerLog whereIsAjax($value)
 * @method static Builder|TrackerLog whereIsJson($value)
 * @method static Builder|TrackerLog whereIsSecure($value)
 * @method static Builder|TrackerLog whereMethod($value)
 * @method static Builder|TrackerLog wherePathId($value)
 * @method static Builder|TrackerLog whereQueryId($value)
 * @method static Builder|TrackerLog whereRoutePathId($value)
 * @method static Builder|TrackerLog whereSessionId($value)
 * @method static Builder|TrackerLog whereUpdatedAt($value)
 * @method static Builder|TrackerLog whereUserId($value)
 * @method static Builder|TrackerLog whereWantsJson($value)
 * @mixin \Eloquent
 * @property-read OAClient|null $oa_client
 * @property-read User $user
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property-read OAClient|null $client
 */
class TrackerLog extends BaseTrackerLog {
	public const CLASS_DESCRIPTION = "A logged API request. ";
	use HasUser;
	protected array $rules = [
		self::FIELD_SESSION_ID => 'nullable|numeric|min:1',
		self::FIELD_PATH_ID => 'nullable|numeric|min:1',
		self::FIELD_QUERY_ID => 'nullable|numeric|min:1',
		self::FIELD_METHOD => 'required|max:10',
		self::FIELD_ROUTE_PATH_ID => 'nullable|numeric|min:1',
		self::FIELD_IS_AJAX => 'required|boolean',
		self::FIELD_IS_SECURE => 'required|boolean',
		self::FIELD_IS_JSON => 'required|boolean',
		self::FIELD_WANTS_JSON => 'required|boolean',
		self::FIELD_ERROR_ID => 'nullable|numeric|min:1',
		self::FIELD_CLIENT_ID => 'nullable|max:80',
		self::FIELD_USER_ID => 'required|numeric|min:1',
	];
	/*
	 * Fillable fields are needed for mass inserts and create
	 * @var array
	 */

	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_USER_ID];
	}
}
