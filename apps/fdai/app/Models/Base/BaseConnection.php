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
use App\Casts\OAuthAuthenticatorCast;
use App\Models\BaseModel;
use App\Models\Connection;
use App\Models\Connector;
use App\Models\ConnectorImport;
use App\Models\ConnectorRequest;
use App\Models\Measurement;
use App\Models\OAClient;
use App\Models\WpPost;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseConnection
 * @property int $id
 * @property string $client_id
 * @property int $user_id
 * @property int $connector_id
 * @property string $connect_status
 * @property string $connect_error
 * @property Carbon $update_requested_at
 * @property string $update_status
 * @property string $update_error
 * @property Carbon $last_successful_updated_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property int $total_measurements_in_last_update
 * @property string $user_message
 * @property Carbon $latest_measurement_at
 * @property array $meta
 * @property Carbon $import_started_at
 * @property Carbon $import_ended_at
 * @property string $reason_for_import
 * @property string $user_error_message
 * @property string $connector_user_id
 * @property string $connector_user_email
 * @property string $internal_error_message
 * @property int $wp_post_id
 * @property int $number_of_connector_imports
 * @property int $number_of_connector_requests
 * @property string $credentials
 * @property Carbon $imported_data_from_at
 * @property Carbon $imported_data_end_at
 * @property int $number_of_measurements
 * @property bool $is_public
 * @property OAClient $oa_client
 * @property Connector $connector
 * @property \App\Models\User $user
 * @property WpPost $wp_post
 * @property Collection|ConnectorImport[] $connector_imports
 * @property Collection|ConnectorRequest[] $connector_requests
 * @property Collection|Measurement[] $measurements
 * @package App\Models\Base
 * @property-read int|null $connector_imports_count
 * @property-read int|null $connector_requests_count
 * @property mixed $raw

 * @property-read int|null $measurements_count
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection newQuery()
 * @method static \Illuminate\Database\Query\Builder|BaseConnection onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection query()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereConnectError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereConnectStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereConnectorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereCredentials($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereImportEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereImportStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereImportedDataEndAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereImportedDataFromAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereInternalErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereLastSuccessfulUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereLatestMeasurementAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereNumberOfConnectorImports($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereNumberOfConnectorRequests($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereNumberOfMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereReasonForImport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereTotalMeasurementsInLastUpdate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereUpdateError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereUpdateRequestedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereUpdateStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereUserErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereUserMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereWpPostId($value)
 * @method static \Illuminate\Database\Query\Builder|BaseConnection withTrashed()
 * @method static \Illuminate\Database\Query\Builder|BaseConnection withoutTrashed()
 * @mixin \Eloquent
 */
abstract class BaseConnection extends BaseModel {
	use SoftDeletes;
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CONNECT_ERROR = 'connect_error';
	public const FIELD_CONNECT_STATUS = 'connect_status';
	public const FIELD_CONNECTOR_ID = 'connector_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_CREDENTIALS = 'credentials';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_ID = 'id';
	public const FIELD_IMPORT_ENDED_AT = 'import_ended_at';
	public const FIELD_IMPORT_STARTED_AT = 'import_started_at';
	public const FIELD_IMPORTED_DATA_END_AT = 'imported_data_end_at';
	public const FIELD_IMPORTED_DATA_FROM_AT = 'imported_data_from_at';
	public const FIELD_INTERNAL_ERROR_MESSAGE = 'internal_error_message';
	public const FIELD_IS_PUBLIC = 'is_public';
	public const FIELD_LAST_SUCCESSFUL_UPDATED_AT = 'last_successful_updated_at';
	public const FIELD_LATEST_MEASUREMENT_AT = 'latest_measurement_at';
	public const FIELD_META = 'meta';
	public const FIELD_NUMBER_OF_CONNECTOR_IMPORTS = 'number_of_connector_imports';
	public const FIELD_NUMBER_OF_CONNECTOR_REQUESTS = 'number_of_connector_requests';
	public const FIELD_NUMBER_OF_MEASUREMENTS = 'number_of_measurements';
	public const FIELD_REASON_FOR_IMPORT = 'reason_for_import';
	public const FIELD_TOTAL_MEASUREMENTS_IN_LAST_UPDATE = 'total_measurements_in_last_update';
	public const FIELD_UPDATE_ERROR = 'update_error';
	public const FIELD_UPDATE_REQUESTED_AT = 'update_requested_at';
	public const FIELD_UPDATE_STATUS = 'update_status';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ERROR_MESSAGE = 'user_error_message';
	public const FIELD_USER_ID = 'user_id';
	public const FIELD_CONNECTOR_USER_ID = 'connector_user_id';
	public const FIELD_CONNECTOR_USER_EMAIL = 'connector_user_email';
	public const FIELD_USER_MESSAGE = 'user_message';
	public const FIELD_WP_POST_ID = 'wp_post_id';
	public const TABLE = 'connections';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_UPDATE_REQUESTED_AT => 'datetime',
        self::FIELD_LAST_SUCCESSFUL_UPDATED_AT => 'datetime',
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
        self::FIELD_LATEST_MEASUREMENT_AT => 'datetime',
        self::FIELD_IMPORT_STARTED_AT => 'datetime',
        self::FIELD_IMPORT_ENDED_AT => 'datetime',
        self::FIELD_IMPORTED_DATA_FROM_AT => 'datetime',
        self::FIELD_IMPORTED_DATA_END_AT => 'datetime',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_CONNECTOR_ID => 'int',
		self::FIELD_CONNECT_ERROR => 'string',
		self::FIELD_CONNECT_STATUS => 'string',
		self::FIELD_CONNECTOR_USER_ID => 'string',
		self::FIELD_CONNECTOR_USER_EMAIL => 'string',
		self::FIELD_CREDENTIALS => OAuthAuthenticatorCast::class,
		self::FIELD_ID => 'int',
		self::FIELD_INTERNAL_ERROR_MESSAGE => 'string',
		self::FIELD_IS_PUBLIC => 'bool',
		self::FIELD_META => 'array',
		self::FIELD_NUMBER_OF_CONNECTOR_IMPORTS => 'int',
		self::FIELD_NUMBER_OF_CONNECTOR_REQUESTS => 'int',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'int',
		self::FIELD_REASON_FOR_IMPORT => 'string',
		self::FIELD_TOTAL_MEASUREMENTS_IN_LAST_UPDATE => 'int',
		self::FIELD_UPDATE_ERROR => 'string',
		self::FIELD_UPDATE_STATUS => 'string',
		self::FIELD_USER_ERROR_MESSAGE => 'string',
		self::FIELD_USER_ID => 'int',
		self::FIELD_USER_MESSAGE => 'string',
		self::FIELD_WP_POST_ID => 'int',	];
	protected array $rules = [
		self::FIELD_CLIENT_ID => 'nullable|max:80',
		self::FIELD_CONNECTOR_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_CONNECT_ERROR => 'nullable|max:65535',
		self::FIELD_CONNECT_STATUS => 'required|max:32',
		self::FIELD_CREDENTIALS => 'nullable|max:65535',
		self::FIELD_IMPORTED_DATA_END_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_IMPORTED_DATA_FROM_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_IMPORT_ENDED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_IMPORT_STARTED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_INTERNAL_ERROR_MESSAGE => 'nullable|max:65535',
		self::FIELD_IS_PUBLIC => 'nullable|boolean',
		self::FIELD_LAST_SUCCESSFUL_UPDATED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_LATEST_MEASUREMENT_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_META => 'nullable|array',
		self::FIELD_NUMBER_OF_CONNECTOR_IMPORTS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_CONNECTOR_REQUESTS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_REASON_FOR_IMPORT => 'nullable|max:255',
		self::FIELD_TOTAL_MEASUREMENTS_IN_LAST_UPDATE => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_UPDATE_ERROR => 'nullable|max:65535',
		self::FIELD_UPDATE_REQUESTED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_UPDATE_STATUS => 'required|max:32',
		self::FIELD_USER_ERROR_MESSAGE => 'nullable|max:65535',
		self::FIELD_USER_ID => 'required|numeric|min:0',
		self::FIELD_USER_MESSAGE => 'nullable|max:255',
		self::FIELD_WP_POST_ID => 'nullable|numeric|min:0',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_CLIENT_ID => '',
		self::FIELD_USER_ID => '',
		self::FIELD_CONNECTOR_ID => 'The id for the connector data source for which the connection is connected',
		self::FIELD_CONNECT_STATUS => 'Indicates whether a connector is currently connected to a service for a user.',
		self::FIELD_CONNECT_ERROR => 'Error message if there is a problem with authorizing this connection.',
		self::FIELD_UPDATE_REQUESTED_AT => 'datetime',
		self::FIELD_UPDATE_STATUS => 'Indicates whether a connector is currently updated.',
		self::FIELD_UPDATE_ERROR => 'Indicates if there was an error during the update.',
		self::FIELD_LAST_SUCCESSFUL_UPDATED_AT => 'datetime',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_TOTAL_MEASUREMENTS_IN_LAST_UPDATE => '',
		self::FIELD_USER_MESSAGE => '',
		self::FIELD_LATEST_MEASUREMENT_AT => 'datetime',
		self::FIELD_IMPORT_STARTED_AT => 'datetime',
		self::FIELD_IMPORT_ENDED_AT => 'datetime',
		self::FIELD_REASON_FOR_IMPORT => '',
		self::FIELD_USER_ERROR_MESSAGE => '',
		self::FIELD_INTERNAL_ERROR_MESSAGE => '',
		self::FIELD_WP_POST_ID => '',
		self::FIELD_NUMBER_OF_CONNECTOR_IMPORTS => 'Number of Connector Imports for this Connection.
                [Formula:
                    update connections
                        left join (
                            select count(id) as total, connection_id
                            from connector_imports
                            group by connection_id
                        )
                        as grouped on connections.id = grouped.connection_id
                    set connections.number_of_connector_imports = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_CONNECTOR_REQUESTS => 'Number of Connector Requests for this Connection.
                [Formula:
                    update connections
                        left join (
                            select count(id) as total, connection_id
                            from connector_requests
                            group by connection_id
                        )
                        as grouped on connections.id = grouped.connection_id
                    set connections.number_of_connector_requests = count(grouped.total)
                ]
                ',
		self::FIELD_CREDENTIALS => 'Encrypted user credentials for accessing third party data',
		self::FIELD_IMPORTED_DATA_FROM_AT => 'Earliest data that we\'ve requested from this data source ',
		self::FIELD_IMPORTED_DATA_END_AT => 'Most recent data that we\'ve requested from this data source ',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'Number of Measurements for this Connection.
                    [Formula: update connections
                        left join (
                            select count(id) as total, connection_id
                            from measurements
                            group by connection_id
                        )
                        as grouped on connections.id = grouped.connection_id
                    set connections.number_of_measurements = count(grouped.total)]',
		self::FIELD_IS_PUBLIC => '',
	];
	protected array $relationshipInfo = [
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => Connection::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => Connection::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'connector' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Connector::class,
			'foreignKeyColumnName' => 'connector_id',
			'foreignKey' => Connection::FIELD_CONNECTOR_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Connector::FIELD_ID,
			'ownerKeyColumnName' => 'connector_id',
			'ownerKey' => Connection::FIELD_CONNECTOR_ID,
			'methodName' => 'connector',
		],
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'user_id',
			'foreignKey' => Connection::FIELD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'user_id',
			'ownerKey' => Connection::FIELD_USER_ID,
			'methodName' => 'user',
		],
		'wp_post' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => WpPost::class,
			'foreignKeyColumnName' => 'wp_post_id',
			'foreignKey' => Connection::FIELD_WP_POST_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => WpPost::FIELD_ID,
			'ownerKeyColumnName' => 'wp_post_id',
			'ownerKey' => Connection::FIELD_WP_POST_ID,
			'methodName' => 'wp_post',
		],
		'connector_imports' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => ConnectorImport::class,
			'foreignKey' => ConnectorImport::FIELD_CONNECTION_ID,
			'localKey' => ConnectorImport::FIELD_ID,
			'methodName' => 'connector_imports',
		],
		'connector_requests' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => ConnectorRequest::class,
			'foreignKey' => ConnectorRequest::FIELD_CONNECTION_ID,
			'localKey' => ConnectorRequest::FIELD_ID,
			'methodName' => 'connector_requests',
		],
		'measurements' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Measurement::class,
			'foreignKey' => Measurement::FIELD_CONNECTION_ID,
			'localKey' => Measurement::FIELD_ID,
			'methodName' => 'measurements',
		],
	];
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, Connection::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			Connection::FIELD_CLIENT_ID);
	}
	public function connector(): BelongsTo{
		return $this->belongsTo(Connector::class, Connection::FIELD_CONNECTOR_ID, Connector::FIELD_ID,
			Connection::FIELD_CONNECTOR_ID);
	}
	public function user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, Connection::FIELD_USER_ID, \App\Models\User::FIELD_ID,
			Connection::FIELD_USER_ID);
	}
	public function wp_post(): BelongsTo{
		return $this->belongsTo(WpPost::class, Connection::FIELD_WP_POST_ID, WpPost::FIELD_ID,
			Connection::FIELD_WP_POST_ID);
	}
	public function connector_imports(): HasMany{
		return $this->hasMany(ConnectorImport::class, ConnectorImport::FIELD_CONNECTION_ID, static::FIELD_ID);
	}
	public function connector_requests(): HasMany{
		return $this->hasMany(ConnectorRequest::class, ConnectorRequest::FIELD_CONNECTION_ID, static::FIELD_ID);
	}
	public function measurements(): HasMany{
		return $this->hasMany(Measurement::class, Measurement::FIELD_CONNECTION_ID, static::FIELD_ID);
	}
}
