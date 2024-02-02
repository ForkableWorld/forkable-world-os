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
use App\Models\ConnectorRequest;
use App\Models\Measurement;
use App\Models\OAClient;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseConnectorImport
 * @property int $id
 * @property string $client_id
 * @property int $connection_id
 * @property int $connector_id
 * @property Carbon $created_at
 * @property Carbon $deleted_at
 * @property Carbon $earliest_measurement_at
 * @property Carbon $import_ended_at
 * @property Carbon $import_started_at
 * @property string $internal_error_message
 * @property Carbon $latest_measurement_at
 * @property int $number_of_measurements
 * @property string $reason_for_import
 * @property bool $success
 * @property Carbon $updated_at
 * @property string $user_error_message
 * @property int $user_id
 * @property array $additional_meta_data
 * @property int $number_of_connector_requests
 * @property Carbon $imported_data_from_at
 * @property Carbon $imported_data_end_at
 * @property string $credentials
 * @property Carbon $connector_requests
 * @property OAClient $oa_client
 * @property Connection $connection
 * @property Connector $connector
 * @property \App\Models\User $user
 * @property Collection|Measurement[] $measurements
 * @package App\Models\Base
 * @property-read int|null $connector_requests_count

 * @property-read int|null $measurements_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorImport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorImport newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseConnectorImport onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorImport query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorImport
 *     whereAdditionalMetaData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorImport whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorImport whereConnectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorImport whereConnectorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorImport
 *     whereConnectorRequests($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorImport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorImport whereCredentials($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorImport whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorImport
 *     whereEarliestMeasurementAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorImport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorImport whereImportEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorImport
 *     whereImportStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorImport
 *     whereImportedDataEndAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorImport
 *     whereImportedDataFromAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorImport
 *     whereInternalErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorImport
 *     whereLatestMeasurementAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorImport
 *     whereNumberOfConnectorRequests($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorImport
 *     whereNumberOfMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorImport
 *     whereReasonForImport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorImport whereSuccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorImport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorImport
 *     whereUserErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorImport whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseConnectorImport withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseConnectorImport withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseConnectorImport extends BaseModel {
	use SoftDeletes;
	public const FIELD_ADDITIONAL_META_DATA = 'additional_meta_data';
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CONNECTION_ID = 'connection_id';
	public const FIELD_CONNECTOR_ID = 'connector_id';
	public const FIELD_CONNECTOR_REQUESTS = 'connector_requests';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_CREDENTIALS = 'credentials';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_EARLIEST_MEASUREMENT_AT = 'earliest_measurement_at';
	public const FIELD_ID = 'id';
	public const FIELD_IMPORT_ENDED_AT = 'import_ended_at';
	public const FIELD_IMPORT_STARTED_AT = 'import_started_at';
	public const FIELD_IMPORTED_DATA_END_AT = 'imported_data_end_at';
	public const FIELD_IMPORTED_DATA_FROM_AT = 'imported_data_from_at';
	public const FIELD_INTERNAL_ERROR_MESSAGE = 'internal_error_message';
	public const FIELD_LATEST_MEASUREMENT_AT = 'latest_measurement_at';
	public const FIELD_NUMBER_OF_CONNECTOR_REQUESTS = 'number_of_connector_requests';
	public const FIELD_NUMBER_OF_MEASUREMENTS = 'number_of_measurements';
	public const FIELD_REASON_FOR_IMPORT = 'reason_for_import';
	public const FIELD_SUCCESS = 'success';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ERROR_MESSAGE = 'user_error_message';
	public const FIELD_USER_ID = 'user_id';
	public const TABLE = 'connector_imports';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
        self::FIELD_EARLIEST_MEASUREMENT_AT => 'datetime',
        self::FIELD_IMPORT_ENDED_AT => 'datetime',
        self::FIELD_IMPORT_STARTED_AT => 'datetime',
        self::FIELD_LATEST_MEASUREMENT_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_IMPORTED_DATA_FROM_AT => 'datetime',
        self::FIELD_IMPORTED_DATA_END_AT => 'datetime',
        self::FIELD_CONNECTOR_REQUESTS => 'datetime',
		self::FIELD_ADDITIONAL_META_DATA => 'json',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_CONNECTION_ID => 'int',
		self::FIELD_CONNECTOR_ID => 'int',
		self::FIELD_CREDENTIALS => 'string',
		self::FIELD_ID => 'int',
		self::FIELD_INTERNAL_ERROR_MESSAGE => 'string',
		self::FIELD_NUMBER_OF_CONNECTOR_REQUESTS => 'int',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'int',
		self::FIELD_REASON_FOR_IMPORT => 'string',
		self::FIELD_SUCCESS => 'bool',
		self::FIELD_USER_ERROR_MESSAGE => 'string',
		self::FIELD_USER_ID => 'int',	];
	protected array $rules = [
		self::FIELD_ADDITIONAL_META_DATA => 'nullable|json',
		self::FIELD_CLIENT_ID => 'nullable|max:80',
		self::FIELD_CONNECTION_ID => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_CONNECTOR_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_CONNECTOR_REQUESTS => 'nullable|date',
		self::FIELD_CREDENTIALS => 'nullable|max:65535',
		self::FIELD_EARLIEST_MEASUREMENT_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_IMPORTED_DATA_END_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_IMPORTED_DATA_FROM_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_IMPORT_ENDED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_IMPORT_STARTED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_INTERNAL_ERROR_MESSAGE => 'nullable|max:65535',
		self::FIELD_LATEST_MEASUREMENT_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_NUMBER_OF_CONNECTOR_REQUESTS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'required|integer|min:0|max:2147483647',
		self::FIELD_REASON_FOR_IMPORT => 'nullable|max:255',
		self::FIELD_SUCCESS => 'nullable|boolean',
		self::FIELD_USER_ERROR_MESSAGE => 'nullable|max:65535',
		self::FIELD_USER_ID => 'required|numeric|min:0',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_CLIENT_ID => '',
		self::FIELD_CONNECTION_ID => '',
		self::FIELD_CONNECTOR_ID => '',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_EARLIEST_MEASUREMENT_AT => 'datetime',
		self::FIELD_IMPORT_ENDED_AT => 'datetime',
		self::FIELD_IMPORT_STARTED_AT => 'datetime',
		self::FIELD_INTERNAL_ERROR_MESSAGE => '',
		self::FIELD_LATEST_MEASUREMENT_AT => 'datetime',
		self::FIELD_NUMBER_OF_MEASUREMENTS => '',
		self::FIELD_REASON_FOR_IMPORT => '',
		self::FIELD_SUCCESS => '',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_USER_ERROR_MESSAGE => '',
		self::FIELD_USER_ID => '',
		self::FIELD_ADDITIONAL_META_DATA => '',
		self::FIELD_NUMBER_OF_CONNECTOR_REQUESTS => 'Number of Connector Requests for this Connector Import.
                [Formula:
                    update connector_imports
                        left join (
                            select count(id) as total, connector_import_id
                            from connector_requests
                            group by connector_import_id
                        )
                        as grouped on connector_imports.id = grouped.connector_import_id
                    set connector_imports.number_of_connector_requests = count(grouped.total)
                ]
                ',
		self::FIELD_IMPORTED_DATA_FROM_AT => 'Earliest data that we\'ve requested from this data source ',
		self::FIELD_IMPORTED_DATA_END_AT => 'Most recent data that we\'ve requested from this data source ',
		self::FIELD_CREDENTIALS => 'Encrypted user credentials for accessing third party data',
		self::FIELD_CONNECTOR_REQUESTS => 'Most recent data that we\'ve requested from this data source ',
	];
	protected array $relationshipInfo = [
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => ConnectorImport::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => ConnectorImport::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'connection' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Connection::class,
			'foreignKeyColumnName' => 'connection_id',
			'foreignKey' => ConnectorImport::FIELD_CONNECTION_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Connection::FIELD_ID,
			'ownerKeyColumnName' => 'connection_id',
			'ownerKey' => ConnectorImport::FIELD_CONNECTION_ID,
			'methodName' => 'connection',
		],
		'connector' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Connector::class,
			'foreignKeyColumnName' => 'connector_id',
			'foreignKey' => ConnectorImport::FIELD_CONNECTOR_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Connector::FIELD_ID,
			'ownerKeyColumnName' => 'connector_id',
			'ownerKey' => ConnectorImport::FIELD_CONNECTOR_ID,
			'methodName' => 'connector',
		],
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'user_id',
			'foreignKey' => ConnectorImport::FIELD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'user_id',
			'ownerKey' => ConnectorImport::FIELD_USER_ID,
			'methodName' => 'user',
		],
		'connector_requests' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => ConnectorRequest::class,
			'foreignKey' => ConnectorRequest::FIELD_CONNECTOR_IMPORT_ID,
			'localKey' => ConnectorRequest::FIELD_ID,
			'methodName' => 'connector_requests',
		],
		'measurements' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Measurement::class,
			'foreignKey' => Measurement::FIELD_CONNECTOR_IMPORT_ID,
			'localKey' => Measurement::FIELD_ID,
			'methodName' => 'measurements',
		],
	];
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, ConnectorImport::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			ConnectorImport::FIELD_CLIENT_ID);
	}
	public function connection(): BelongsTo{
		return $this->belongsTo(Connection::class, ConnectorImport::FIELD_CONNECTION_ID, Connection::FIELD_ID,
			ConnectorImport::FIELD_CONNECTION_ID);
	}
	public function connector(): BelongsTo{
		return $this->belongsTo(Connector::class, ConnectorImport::FIELD_CONNECTOR_ID, Connector::FIELD_ID,
			ConnectorImport::FIELD_CONNECTOR_ID);
	}
	public function user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, ConnectorImport::FIELD_USER_ID, \App\Models\User::FIELD_ID,
			ConnectorImport::FIELD_USER_ID);
	}
	public function connector_requests(): HasMany{
		return $this->hasMany(ConnectorRequest::class, ConnectorRequest::FIELD_CONNECTOR_IMPORT_ID, static::FIELD_ID);
	}
	public function measurements(): HasMany{
		return $this->hasMany(Measurement::class, Measurement::FIELD_CONNECTOR_IMPORT_ID, static::FIELD_ID);
	}
}
