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
use App\Models\Connection;
use App\Models\Connector;
use App\Models\ConnectorImport;
use App\Models\Measurement;
use App\Models\OAClient;
use App\Models\Unit;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Models\VariableCategory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseMeasurement
 * @property int $id
 * @property int $user_id
 * @property string $client_id
 * @property int $connector_id
 * @property int $variable_id
 * @property int $start_time
 * @property float $value
 * @property int $unit_id
 * @property float $original_value
 * @property int $original_unit_id
 * @property int $duration
 * @property string $note
 * @property float $latitude
 * @property float $longitude
 * @property string $location
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $error
 * @property int $variable_category_id
 * @property Carbon $deleted_at
 * @property string $source_name
 * @property int $user_variable_id
 * @property Carbon $start_at
 * @property int $connection_id
 * @property int $connector_import_id
 * @property string $deletion_reason
 * @property Carbon $original_start_at
 * @property OAClient $oa_client
 * @property Connection $connection
 * @property ConnectorImport $connector_import
 * @property Connector $connector
 * @property Unit $original_unit
 * @property Unit $unit
 * @property \App\Models\User $user
 * @property UserVariable $user_variable
 * @property VariableCategory $variable_category
 * @property Variable $variable
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurement newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseMeasurement onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurement query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurement whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurement whereConnectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurement whereConnectorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurement whereConnectorImportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurement whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurement whereDeletionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurement whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurement whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurement whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurement whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurement whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurement whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurement whereOriginalUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurement whereOriginalValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurement whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurement whereSourceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurement whereStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurement whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurement whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurement whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurement whereUserVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurement whereValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurement whereVariableCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurement whereVariableId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseMeasurement withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseMeasurement withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 * @method static \Illuminate\Database\Eloquent\Builder|BaseMeasurement whereOriginalStartAt($value)
 */
abstract class BaseMeasurement extends BaseModel {
	use SoftDeletes;
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CONNECTION_ID = 'connection_id';
	public const FIELD_CONNECTOR_ID = 'connector_id';
	public const FIELD_CONNECTOR_IMPORT_ID = 'connector_import_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_DELETION_REASON = 'deletion_reason';
	public const FIELD_DURATION = 'duration';
	public const FIELD_ERROR = 'error';
	public const FIELD_ID = 'id';
	public const FIELD_LATITUDE = 'latitude';
	public const FIELD_LOCATION = 'location';
	public const FIELD_LONGITUDE = 'longitude';
	public const FIELD_NOTE = 'note';
	public const FIELD_ORIGINAL_START_AT = 'original_start_at';
	public const FIELD_ORIGINAL_UNIT_ID = 'original_unit_id';
	public const FIELD_ORIGINAL_VALUE = 'original_value';
	public const FIELD_SOURCE_NAME = 'source_name';
	public const FIELD_START_AT = 'start_at';
	public const FIELD_START_TIME = 'start_time';
	public const FIELD_UNIT_ID = 'unit_id';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ID = 'user_id';
	public const FIELD_USER_VARIABLE_ID = 'user_variable_id';
	public const FIELD_VALUE = 'value';
	public const FIELD_VARIABLE_CATEGORY_ID = 'variable_category_id';
	public const FIELD_VARIABLE_ID = 'variable_id';
	public const TABLE = 'measurements';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = 'Measurements are quantities recorded at specific times.   Sleep minutes, apples eaten, or mood rating are examples of variables. ';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
        self::FIELD_START_AT => 'datetime',
        self::FIELD_ORIGINAL_START_AT => 'datetime',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_CONNECTION_ID => 'int',
		self::FIELD_CONNECTOR_ID => 'int',
		self::FIELD_CONNECTOR_IMPORT_ID => 'int',
		self::FIELD_DELETION_REASON => 'string',
		self::FIELD_DURATION => 'int',
		self::FIELD_ERROR => 'string',
		self::FIELD_ID => 'int',
		self::FIELD_LATITUDE => 'float',
		self::FIELD_LOCATION => 'string',
		self::FIELD_LONGITUDE => 'float',
		self::FIELD_NOTE => 'string',
		self::FIELD_ORIGINAL_UNIT_ID => 'int',
		self::FIELD_ORIGINAL_VALUE => 'float',
		self::FIELD_SOURCE_NAME => 'string',
		self::FIELD_START_TIME => 'int',
		self::FIELD_UNIT_ID => 'int',
		self::FIELD_USER_ID => 'int',
		self::FIELD_USER_VARIABLE_ID => 'int',
		self::FIELD_VALUE => 'float',
		self::FIELD_VARIABLE_CATEGORY_ID => 'int',
		self::FIELD_VARIABLE_ID => 'int',	];
	protected array $rules = [
		self::FIELD_CLIENT_ID => 'required|max:80',
		self::FIELD_CONNECTION_ID => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_CONNECTOR_ID => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_CONNECTOR_IMPORT_ID => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_DELETION_REASON => 'nullable|max:280',
		self::FIELD_DURATION => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_ERROR => 'nullable|max:65535',
		self::FIELD_LATITUDE => 'nullable|numeric',
		self::FIELD_LOCATION => 'nullable|max:255',
		self::FIELD_LONGITUDE => 'nullable|numeric',
		self::FIELD_NOTE => 'nullable|max:65535',
		self::FIELD_ORIGINAL_START_AT => 'required|date',
		self::FIELD_ORIGINAL_UNIT_ID => 'required|integer|min:0|max:65535',
		self::FIELD_ORIGINAL_VALUE => 'required|numeric',
		self::FIELD_SOURCE_NAME => 'nullable|max:80',
		self::FIELD_START_AT => 'required|date',
		self::FIELD_START_TIME => 'required|integer|min:0|max:2147483647',
		self::FIELD_UNIT_ID => 'required|integer|min:0|max:65535',
		self::FIELD_USER_ID => 'required|numeric|min:0',
		self::FIELD_USER_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_VALUE => 'required|numeric',
		self::FIELD_VARIABLE_CATEGORY_ID => 'required|boolean',
		self::FIELD_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_USER_ID => '',
		self::FIELD_CLIENT_ID => '',
		self::FIELD_CONNECTOR_ID => 'The id for the connector data source from which the measurement was obtained',
		self::FIELD_VARIABLE_ID => 'ID of the variable for which we are creating the measurement records',
		self::FIELD_START_TIME => 'Start time for the measurement event in ISO 8601',
		self::FIELD_VALUE => 'The value of the measurement after conversion to the default unit for that variable',
		self::FIELD_UNIT_ID => 'The default unit for the variable',
		self::FIELD_ORIGINAL_VALUE => 'Value of measurement as originally posted (before conversion to default unit)',
		self::FIELD_ORIGINAL_UNIT_ID => 'Unit id of measurement as originally submitted',
		self::FIELD_DURATION => 'Duration of the event being measurement in seconds',
		self::FIELD_NOTE => 'An optional note the user may include with their measurement',
		self::FIELD_LATITUDE => 'Latitude at which the measurement was taken',
		self::FIELD_LONGITUDE => 'Longitude at which the measurement was taken',
		self::FIELD_LOCATION => 'location',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_ERROR => 'An error message if there is a problem with the measurement',
		self::FIELD_VARIABLE_CATEGORY_ID => 'Variable category ID',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_SOURCE_NAME => 'Name of the application or device',
		self::FIELD_USER_VARIABLE_ID => '',
		self::FIELD_START_AT => 'datetime',
		self::FIELD_CONNECTION_ID => '',
		self::FIELD_CONNECTOR_IMPORT_ID => '',
		self::FIELD_DELETION_REASON => 'The reason the variable was deleted.',
		self::FIELD_ORIGINAL_START_AT => 'datetime',
	];
	protected array $relationshipInfo = [
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => Measurement::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => Measurement::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'connection' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Connection::class,
			'foreignKeyColumnName' => 'connection_id',
			'foreignKey' => Measurement::FIELD_CONNECTION_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Connection::FIELD_ID,
			'ownerKeyColumnName' => 'connection_id',
			'ownerKey' => Measurement::FIELD_CONNECTION_ID,
			'methodName' => 'connection',
		],
		'connector_import' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => ConnectorImport::class,
			'foreignKeyColumnName' => 'connector_import_id',
			'foreignKey' => Measurement::FIELD_CONNECTOR_IMPORT_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => ConnectorImport::FIELD_ID,
			'ownerKeyColumnName' => 'connector_import_id',
			'ownerKey' => Measurement::FIELD_CONNECTOR_IMPORT_ID,
			'methodName' => 'connector_import',
		],
		'connector' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Connector::class,
			'foreignKeyColumnName' => 'connector_id',
			'foreignKey' => Measurement::FIELD_CONNECTOR_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Connector::FIELD_ID,
			'ownerKeyColumnName' => 'connector_id',
			'ownerKey' => Measurement::FIELD_CONNECTOR_ID,
			'methodName' => 'connector',
		],
		'original_unit' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Unit::class,
			'foreignKeyColumnName' => 'original_unit_id',
			'foreignKey' => Measurement::FIELD_ORIGINAL_UNIT_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Unit::FIELD_ID,
			'ownerKeyColumnName' => 'original_unit_id',
			'ownerKey' => Measurement::FIELD_ORIGINAL_UNIT_ID,
			'methodName' => 'original_unit',
		],
		'unit' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Unit::class,
			'foreignKeyColumnName' => 'unit_id',
			'foreignKey' => Measurement::FIELD_UNIT_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Unit::FIELD_ID,
			'ownerKeyColumnName' => 'unit_id',
			'ownerKey' => Measurement::FIELD_UNIT_ID,
			'methodName' => 'unit',
		],
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'user_id',
			'foreignKey' => Measurement::FIELD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'user_id',
			'ownerKey' => Measurement::FIELD_USER_ID,
			'methodName' => 'user',
		],
		'user_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => UserVariable::class,
			'foreignKeyColumnName' => 'user_variable_id',
			'foreignKey' => Measurement::FIELD_USER_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => UserVariable::FIELD_ID,
			'ownerKeyColumnName' => 'user_variable_id',
			'ownerKey' => Measurement::FIELD_USER_VARIABLE_ID,
			'methodName' => 'user_variable',
		],
		'variable_category' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => VariableCategory::class,
			'foreignKeyColumnName' => 'variable_category_id',
			'foreignKey' => Measurement::FIELD_VARIABLE_CATEGORY_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => VariableCategory::FIELD_ID,
			'ownerKeyColumnName' => 'variable_category_id',
			'ownerKey' => Measurement::FIELD_VARIABLE_CATEGORY_ID,
			'methodName' => 'variable_category',
		],
		'variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'variable_id',
			'foreignKey' => Measurement::FIELD_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'variable_id',
			'ownerKey' => Measurement::FIELD_VARIABLE_ID,
			'methodName' => 'variable',
		],
	];
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, Measurement::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			Measurement::FIELD_CLIENT_ID);
	}
	public function connection(): BelongsTo{
		return $this->belongsTo(Connection::class, Measurement::FIELD_CONNECTION_ID, Connection::FIELD_ID,
			Measurement::FIELD_CONNECTION_ID);
	}
	public function connector_import(): BelongsTo{
		return $this->belongsTo(ConnectorImport::class, Measurement::FIELD_CONNECTOR_IMPORT_ID,
			ConnectorImport::FIELD_ID, Measurement::FIELD_CONNECTOR_IMPORT_ID);
	}
	public function connector(): BelongsTo{
		return $this->belongsTo(Connector::class, Measurement::FIELD_CONNECTOR_ID, Connector::FIELD_ID,
			Measurement::FIELD_CONNECTOR_ID);
	}
	public function original_unit(): BelongsTo{
		return $this->belongsTo(Unit::class, Measurement::FIELD_ORIGINAL_UNIT_ID, Unit::FIELD_ID,
			Measurement::FIELD_ORIGINAL_UNIT_ID);
	}
	public function unit(): BelongsTo{
		return $this->belongsTo(Unit::class, Measurement::FIELD_UNIT_ID, Unit::FIELD_ID, Measurement::FIELD_UNIT_ID);
	}
	public function user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, Measurement::FIELD_USER_ID, \App\Models\User::FIELD_ID,
			Measurement::FIELD_USER_ID);
	}
	public function user_variable(): BelongsTo{
		return $this->belongsTo(UserVariable::class, Measurement::FIELD_USER_VARIABLE_ID, UserVariable::FIELD_ID,
			Measurement::FIELD_USER_VARIABLE_ID);
	}
	public function variable_category(): BelongsTo{
		return $this->belongsTo(VariableCategory::class, Measurement::FIELD_VARIABLE_CATEGORY_ID,
			VariableCategory::FIELD_ID, Measurement::FIELD_VARIABLE_CATEGORY_ID);
	}
	public function variable(): BelongsTo{
		return $this->belongsTo(Variable::class, Measurement::FIELD_VARIABLE_ID, Variable::FIELD_ID,
			Measurement::FIELD_VARIABLE_ID);
	}
}
