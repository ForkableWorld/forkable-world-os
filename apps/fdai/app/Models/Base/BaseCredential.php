<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

/** @noinspection PhpMissingDocCommentInspection */
/** @noinspection PhpUnused */
/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
/** Created by Reliese Model.
 */
namespace App\Models\Base;
use App\Models\BaseModel;
use App\Models\Credential;
use App\Models\OAClient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseCredential
 * @property int $user_id
 * @property int $connector_id
 * @property string $attr_key
 * @property string $attr_value
 * @property string $status
 * @property string $message
 * @property Carbon $expires_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property string $client_id
 * @property OAClient $oa_client
 * @property User $user
 * @package App\Models\Base
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCredential newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCredential newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseCredential onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCredential query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCredential whereAttrKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCredential whereAttrValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCredential whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCredential whereConnectorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCredential whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCredential whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCredential whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCredential whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCredential whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCredential whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCredential whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseCredential withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseCredential withoutTrashed()
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 */
abstract class BaseCredential extends BaseModel {
	use SoftDeletes;
	public const FIELD_USER_ID = 'user_id';
	public const FIELD_CONNECTOR_ID = 'connector_id';
	public const FIELD_ATTR_KEY = 'attr_key';
	public const FIELD_ATTR_VALUE = 'attr_value';
	public const FIELD_STATUS = 'status';
	public const FIELD_MESSAGE = 'message';
	public const FIELD_EXPIRES_AT = 'expires_at';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_CLIENT_ID = 'client_id';
	protected $table = 'credentials';
	public const TABLE = 'credentials';
	public $incrementing = false;
	protected $casts = [
        self::FIELD_EXPIRES_AT => 'datetime',
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_USER_ID => 'int',
		self::FIELD_CONNECTOR_ID => 'int',
		self::FIELD_ATTR_VALUE => 'varbinary',	];
	protected array $rules = [
		'user_id' => 'required|numeric|min:0',
		'connector_id' => 'required|integer|min:0|max:2147483647',
		'attr_key' => 'required|max:16',
		'status' => 'nullable|max:32',
		'message' => 'nullable|max:16777215',
		'expires_at' => 'nullable|datetime',
		'client_id' => 'nullable|max:255',
	];
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, Credential::FIELD_CLIENT_ID);
	}
	public function user(): BelongsTo{
		return $this->belongsTo(User::class, Credential::FIELD_USER_ID);
	}
}
