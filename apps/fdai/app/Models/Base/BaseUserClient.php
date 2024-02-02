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
use App\Models\OAClient;
use App\Models\UserClient;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseUserClient
 * @property int $id
 * @property string $client_id
 * @property Carbon $created_at
 * @property Carbon $deleted_at
 * @property Carbon $earliest_measurement_at
 * @property Carbon $latest_measurement_at
 * @property int $number_of_measurements
 * @property Carbon $updated_at
 * @property int $user_id
 * @property OAClient $oa_client
 * @property \App\Models\User $user
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserClient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserClient newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseUserClient onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserClient query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserClient whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserClient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserClient whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserClient
 *     whereEarliestMeasurementAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserClient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserClient
 *     whereLatestMeasurementAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserClient
 *     whereNumberOfMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserClient whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserClient whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseUserClient withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseUserClient withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseUserClient extends BaseModel {
	use SoftDeletes;
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_EARLIEST_MEASUREMENT_AT = 'earliest_measurement_at';
	public const FIELD_ID = 'id';
	public const FIELD_LATEST_MEASUREMENT_AT = 'latest_measurement_at';
	public const FIELD_NUMBER_OF_MEASUREMENTS = 'number_of_measurements';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ID = 'user_id';
	public const TABLE = 'user_clients';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
        self::FIELD_EARLIEST_MEASUREMENT_AT => 'datetime',
        self::FIELD_LATEST_MEASUREMENT_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_ID => 'int',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'int',
		self::FIELD_USER_ID => 'int',	];
	protected array $rules = [
		self::FIELD_CLIENT_ID => 'nullable|max:80',
		self::FIELD_EARLIEST_MEASUREMENT_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_LATEST_MEASUREMENT_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_USER_ID => 'required|numeric|min:0',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_CLIENT_ID => '',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_EARLIEST_MEASUREMENT_AT => 'Earliest measurement time for this variable and client',
		self::FIELD_LATEST_MEASUREMENT_AT => 'Earliest measurement time for this variable and client',
		self::FIELD_NUMBER_OF_MEASUREMENTS => '',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_USER_ID => '',
	];
	protected array $relationshipInfo = [
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => UserClient::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => UserClient::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'user_id',
			'foreignKey' => UserClient::FIELD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'user_id',
			'ownerKey' => UserClient::FIELD_USER_ID,
			'methodName' => 'user',
		],
	];
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, UserClient::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			UserClient::FIELD_CLIENT_ID);
	}
	public function user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, UserClient::FIELD_USER_ID, \App\Models\User::FIELD_ID,
			UserClient::FIELD_USER_ID);
	}
}
