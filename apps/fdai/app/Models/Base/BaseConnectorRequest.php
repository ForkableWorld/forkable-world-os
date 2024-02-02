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
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseConnectorRequest
 * @property int $id
 * @property int $connector_id
 * @property int $user_id
 * @property int $connection_id
 * @property int $connector_import_id
 * @property string $method
 * @property int $code
 * @property string $uri
 * @property string $response_body
 * @property string $request_body
 * @property string $request_headers
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property string $content_type
 * @property Carbon $imported_data_from_at
 * @property Connection $connection
 * @property ConnectorImport $connector_import
 * @property Connector $connector
 * @property \App\Models\User $user
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorRequest newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseConnectorRequest onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorRequest whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorRequest whereConnectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorRequest whereConnectorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorRequest
 *     whereConnectorImportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorRequest whereContentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorRequest whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorRequest
 *     whereImportedDataFromAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorRequest whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorRequest whereRequestBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorRequest
 *     whereRequestHeaders($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorRequest whereResponseBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorRequest whereUri($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorRequest whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseConnectorRequest withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseConnectorRequest withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseConnectorRequest extends BaseModel {
	use SoftDeletes;
	public const FIELD_CODE = 'code';
	public const FIELD_CONNECTION_ID = 'connection_id';
	public const FIELD_CONNECTOR_ID = 'connector_id';
	public const FIELD_CONNECTOR_IMPORT_ID = 'connector_import_id';
	public const FIELD_CONTENT_TYPE = 'content_type';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_ID = 'id';
	public const FIELD_IMPORTED_DATA_FROM_AT = 'imported_data_from_at';
	public const FIELD_METHOD = 'method';
	public const FIELD_REQUEST_BODY = 'request_body';
	public const FIELD_REQUEST_HEADERS = 'request_headers';
	public const FIELD_RESPONSE_BODY = 'response_body';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_URI = 'uri';
	public const FIELD_USER_ID = 'user_id';
	public const TABLE = 'connector_requests';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
        self::FIELD_IMPORTED_DATA_FROM_AT => 'datetime',
		self::FIELD_CODE => 'int',
		self::FIELD_CONNECTION_ID => 'int',
		self::FIELD_CONNECTOR_ID => 'int',
		self::FIELD_CONNECTOR_IMPORT_ID => 'int',
		self::FIELD_CONTENT_TYPE => 'string',
		self::FIELD_ID => 'int',
		self::FIELD_METHOD => 'string',
		self::FIELD_REQUEST_BODY => 'string',
		self::FIELD_REQUEST_HEADERS => 'string',
		self::FIELD_RESPONSE_BODY => 'string',
		self::FIELD_URI => 'string',
		self::FIELD_USER_ID => 'int',	];
	protected array $rules = [
		self::FIELD_CODE => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_CONNECTION_ID => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_CONNECTOR_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_CONNECTOR_IMPORT_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_CONTENT_TYPE => 'nullable|max:100',
		self::FIELD_IMPORTED_DATA_FROM_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_METHOD => 'required|max:10',
		self::FIELD_REQUEST_BODY => 'nullable|max:65535',
		self::FIELD_REQUEST_HEADERS => 'required|max:65535',
		self::FIELD_RESPONSE_BODY => 'nullable|max:16777215',
		self::FIELD_URI => 'required|max:2083',
		self::FIELD_USER_ID => 'required|numeric|min:0',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_CONNECTOR_ID => '',
		self::FIELD_USER_ID => '',
		self::FIELD_CONNECTION_ID => '',
		self::FIELD_CONNECTOR_IMPORT_ID => '',
		self::FIELD_METHOD => '',
		self::FIELD_CODE => '',
		self::FIELD_URI => '',
		self::FIELD_RESPONSE_BODY => '',
		self::FIELD_REQUEST_BODY => '',
		self::FIELD_REQUEST_HEADERS => '',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CONTENT_TYPE => '',
		self::FIELD_IMPORTED_DATA_FROM_AT => 'Earliest data that we\'ve requested from this data source ',
	];
	protected array $relationshipInfo = [
		'connection' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Connection::class,
			'foreignKeyColumnName' => 'connection_id',
			'foreignKey' => ConnectorRequest::FIELD_CONNECTION_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Connection::FIELD_ID,
			'ownerKeyColumnName' => 'connection_id',
			'ownerKey' => ConnectorRequest::FIELD_CONNECTION_ID,
			'methodName' => 'connection',
		],
		'connector_import' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => ConnectorImport::class,
			'foreignKeyColumnName' => 'connector_import_id',
			'foreignKey' => ConnectorRequest::FIELD_CONNECTOR_IMPORT_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => ConnectorImport::FIELD_ID,
			'ownerKeyColumnName' => 'connector_import_id',
			'ownerKey' => ConnectorRequest::FIELD_CONNECTOR_IMPORT_ID,
			'methodName' => 'connector_import',
		],
		'connector' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Connector::class,
			'foreignKeyColumnName' => 'connector_id',
			'foreignKey' => ConnectorRequest::FIELD_CONNECTOR_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Connector::FIELD_ID,
			'ownerKeyColumnName' => 'connector_id',
			'ownerKey' => ConnectorRequest::FIELD_CONNECTOR_ID,
			'methodName' => 'connector',
		],
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'user_id',
			'foreignKey' => ConnectorRequest::FIELD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'user_id',
			'ownerKey' => ConnectorRequest::FIELD_USER_ID,
			'methodName' => 'user',
		],
	];
	public function connection(): BelongsTo{
		return $this->belongsTo(Connection::class, ConnectorRequest::FIELD_CONNECTION_ID, Connection::FIELD_ID,
			ConnectorRequest::FIELD_CONNECTION_ID);
	}
	public function connector_import(): BelongsTo{
		return $this->belongsTo(ConnectorImport::class, ConnectorRequest::FIELD_CONNECTOR_IMPORT_ID,
			ConnectorImport::FIELD_ID, ConnectorRequest::FIELD_CONNECTOR_IMPORT_ID);
	}
	public function connector(): BelongsTo{
		return $this->belongsTo(Connector::class, ConnectorRequest::FIELD_CONNECTOR_ID, Connector::FIELD_ID,
			ConnectorRequest::FIELD_CONNECTOR_ID);
	}
	public function user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, ConnectorRequest::FIELD_USER_ID, \App\Models\User::FIELD_ID,
			ConnectorRequest::FIELD_USER_ID);
	}
}
