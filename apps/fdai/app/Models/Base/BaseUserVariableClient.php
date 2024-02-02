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
use App\Models\UserVariable;
use App\Models\UserVariableClient;
use App\Models\Variable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseUserVariableClient
 * @property int $id
 * @property string $client_id
 * @property Carbon $created_at
 * @property Carbon $deleted_at
 * @property Carbon $earliest_measurement_at
 * @property Carbon $latest_measurement_at
 * @property int $number_of_measurements
 * @property Carbon $updated_at
 * @property int $user_id
 * @property int $user_variable_id
 * @property int $variable_id
 * @property OAClient $oa_client
 * @property \App\Models\User $user
 * @property UserVariable $user_variable
 * @property Variable $variable
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableClient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableClient newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseUserVariableClient onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableClient query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableClient whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableClient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableClient whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableClient
 *     whereEarliestMeasurementAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableClient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableClient
 *     whereLatestMeasurementAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableClient
 *     whereNumberOfMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableClient whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableClient whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableClient
 *     whereUserVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableClient whereVariableId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseUserVariableClient withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseUserVariableClient withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseUserVariableClient extends BaseModel {
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
	public const FIELD_USER_VARIABLE_ID = 'user_variable_id';
	public const FIELD_VARIABLE_ID = 'variable_id';
	public const TABLE = 'user_variable_clients';
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
		self::FIELD_USER_ID => 'int',
		self::FIELD_USER_VARIABLE_ID => 'int',
		self::FIELD_VARIABLE_ID => 'int',	];
	protected array $rules = [
		self::FIELD_CLIENT_ID => 'required|max:80',
		self::FIELD_EARLIEST_MEASUREMENT_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_LATEST_MEASUREMENT_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_USER_ID => 'required|numeric|min:0',
		self::FIELD_USER_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
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
		self::FIELD_USER_VARIABLE_ID => '',
		self::FIELD_VARIABLE_ID => 'Id of variable',
	];
	protected array $relationshipInfo = [
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => UserVariableClient::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => UserVariableClient::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'user_id',
			'foreignKey' => UserVariableClient::FIELD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'user_id',
			'ownerKey' => UserVariableClient::FIELD_USER_ID,
			'methodName' => 'user',
		],
		'user_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => UserVariable::class,
			'foreignKeyColumnName' => 'user_variable_id',
			'foreignKey' => UserVariableClient::FIELD_USER_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => UserVariable::FIELD_ID,
			'ownerKeyColumnName' => 'user_variable_id',
			'ownerKey' => UserVariableClient::FIELD_USER_VARIABLE_ID,
			'methodName' => 'user_variable',
		],
		'variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'variable_id',
			'foreignKey' => UserVariableClient::FIELD_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'variable_id',
			'ownerKey' => UserVariableClient::FIELD_VARIABLE_ID,
			'methodName' => 'variable',
		],
	];
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, UserVariableClient::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			UserVariableClient::FIELD_CLIENT_ID);
	}
	public function user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, UserVariableClient::FIELD_USER_ID, \App\Models\User::FIELD_ID,
			UserVariableClient::FIELD_USER_ID);
	}
	public function user_variable(): BelongsTo{
		return $this->belongsTo(UserVariable::class, UserVariableClient::FIELD_USER_VARIABLE_ID, UserVariable::FIELD_ID,
			UserVariableClient::FIELD_USER_VARIABLE_ID);
	}
	public function variable(): BelongsTo{
		return $this->belongsTo(Variable::class, UserVariableClient::FIELD_VARIABLE_ID, Variable::FIELD_ID,
			UserVariableClient::FIELD_VARIABLE_ID);
	}
}
