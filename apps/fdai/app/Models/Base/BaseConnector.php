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
use App\Models\WpPost;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseConnector
 * @property int $id
 * @property string $name
 * @property string $display_name
 * @property string $image
 * @property string $get_it_url
 * @property string $short_description
 * @property string $long_description
 * @property bool $enabled
 * @property bool $available_outside_us
 * @property bool $oauth
 * @property bool $qm_client
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $client_id
 * @property Carbon $deleted_at
 * @property int $wp_post_id
 * @property int $number_of_connections
 * @property int $number_of_connector_imports
 * @property int $number_of_connector_requests
 * @property int $number_of_measurements
 * @property bool $is_public
 * @property int $sort_order
 * @property OAClient $oa_client
 * @property WpPost $wp_post
 * @property Collection|Connection[] $connections
 * @property Collection|ConnectorImport[] $connector_imports
 * @property Collection|ConnectorRequest[] $connector_requests
 * @property Collection|Measurement[] $measurements
 * @package App\Models\Base
 * @property-read int|null $connections_count
 * @property-read int|null $connector_imports_count
 * @property-read int|null $connector_requests_count
 * @property mixed $raw

 * @property-read int|null $measurements_count
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnector newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnector newQuery()
 * @method static \Illuminate\Database\Query\Builder|BaseConnector onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnector query()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnector whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnector whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnector whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnector whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnector whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnector whereGetItUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnector whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnector whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnector whereLongDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnector whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnector whereNumberOfConnections($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnector whereNumberOfConnectorImports($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnector whereNumberOfConnectorRequests($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnector whereNumberOfMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnector whereOauth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnector whereQmClient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnector whereShortDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnector whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnector whereWpPostId($value)
 * @method static \Illuminate\Database\Query\Builder|BaseConnector withTrashed()
 * @method static \Illuminate\Database\Query\Builder|BaseConnector withoutTrashed()
 * @mixin \Eloquent
 */
abstract class BaseConnector extends BaseModel {
	use SoftDeletes;
    public const FIELD_AVAILABLE_OUTSIDE_US = 'available_outside_us';
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_DISPLAY_NAME = 'display_name';
	public const FIELD_ENABLED = 'enabled';
	public const FIELD_GET_IT_URL = 'get_it_url';
	public const FIELD_ID = 'id';
	public const FIELD_IMAGE = 'image';
	public const FIELD_IS_PUBLIC = 'is_public';
	public const FIELD_LONG_DESCRIPTION = 'long_description';
	public const FIELD_NAME = 'name';
	public const FIELD_NUMBER_OF_CONNECTIONS = 'number_of_connections';
	public const FIELD_NUMBER_OF_CONNECTOR_IMPORTS = 'number_of_connector_imports';
	public const FIELD_NUMBER_OF_CONNECTOR_REQUESTS = 'number_of_connector_requests';
	public const FIELD_NUMBER_OF_MEASUREMENTS = 'number_of_measurements';
	public const FIELD_OAUTH = 'oauth';
	public const FIELD_QM_CLIENT = 'qm_client';
	public const FIELD_SHORT_DESCRIPTION = 'short_description';
	public const FIELD_SORT_ORDER = 'sort_order';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_WP_POST_ID = 'wp_post_id';
	public const TABLE = 'connectors';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_DISPLAY_NAME => 'string',
		self::FIELD_ENABLED => 'bool',
		self::FIELD_GET_IT_URL => 'string',
		self::FIELD_ID => 'int',
		self::FIELD_IMAGE => 'string',
		self::FIELD_IS_PUBLIC => 'bool',
		self::FIELD_LONG_DESCRIPTION => 'string',
		self::FIELD_NAME => 'string',
		self::FIELD_NUMBER_OF_CONNECTIONS => 'int',
		self::FIELD_NUMBER_OF_CONNECTOR_IMPORTS => 'int',
		self::FIELD_NUMBER_OF_CONNECTOR_REQUESTS => 'int',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'int',
		self::FIELD_OAUTH => 'bool',
		self::FIELD_QM_CLIENT => 'bool',
		self::FIELD_SHORT_DESCRIPTION => 'string',
		self::FIELD_SORT_ORDER => 'int',
		self::FIELD_WP_POST_ID => 'int',	];
	protected array $rules = [
		self::FIELD_CLIENT_ID => 'nullable|max:80',
		self::FIELD_DISPLAY_NAME => 'required|max:30',
		self::FIELD_ENABLED => 'required|boolean',
		self::FIELD_GET_IT_URL => 'nullable|max:2083',
		self::FIELD_IMAGE => 'required|max:2083',
		self::FIELD_IS_PUBLIC => 'nullable|boolean',
		self::FIELD_LONG_DESCRIPTION => 'required',
		self::FIELD_NAME => 'required|max:30',
		self::FIELD_NUMBER_OF_CONNECTIONS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_CONNECTOR_IMPORTS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_CONNECTOR_REQUESTS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_OAUTH => 'required|boolean',
		self::FIELD_QM_CLIENT => 'nullable|boolean',
		self::FIELD_SHORT_DESCRIPTION => 'required|max:65535',
		self::FIELD_SORT_ORDER => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_WP_POST_ID => 'nullable|numeric|min:0',
	];
	protected $hints = [
		self::FIELD_ID => 'Connector ID number',
		self::FIELD_NAME => 'Lowercase system name for the data source',
		self::FIELD_DISPLAY_NAME => 'Pretty display name for the data source',
		self::FIELD_IMAGE => 'URL to the image of the connector logo',
		self::FIELD_GET_IT_URL => 'URL to a site where one can get this device or application',
		self::FIELD_SHORT_DESCRIPTION => 'Short description of the service (such as the categories it tracks)',
		self::FIELD_LONG_DESCRIPTION => 'Longer paragraph description of the data provider',
		self::FIELD_ENABLED => 'Set to 1 if the connector should be returned when listing connectors',
		self::FIELD_OAUTH => 'Set to 1 if the connector uses OAuth authentication as opposed to username/password',
		self::FIELD_QM_CLIENT => 'Whether its a connector or one of our clients',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_CLIENT_ID => '',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_WP_POST_ID => '',
		self::FIELD_NUMBER_OF_CONNECTIONS => 'Number of Connections for this Connector.
                [Formula:
                    update connectors
                        left join (
                            select count(id) as total, connector_id
                            from connections
                            group by connector_id
                        )
                        as grouped on connectors.id = grouped.connector_id
                    set connectors.number_of_connections = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_CONNECTOR_IMPORTS => 'Number of Connector Imports for this Connector.
                [Formula:
                    update connectors
                        left join (
                            select count(id) as total, connector_id
                            from connector_imports
                            group by connector_id
                        )
                        as grouped on connectors.id = grouped.connector_id
                    set connectors.number_of_connector_imports = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_CONNECTOR_REQUESTS => 'Number of Connector Requests for this Connector.
                [Formula:
                    update connectors
                        left join (
                            select count(id) as total, connector_id
                            from connector_requests
                            group by connector_id
                        )
                        as grouped on connectors.id = grouped.connector_id
                    set connectors.number_of_connector_requests = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'Number of Measurements for this Connector.
                    [Formula: update connectors
                        left join (
                            select count(id) as total, connector_id
                            from measurements
                            group by connector_id
                        )
                        as grouped on connectors.id = grouped.connector_id
                    set connectors.number_of_measurements = count(grouped.total)]',
		self::FIELD_IS_PUBLIC => '',
		self::FIELD_SORT_ORDER => '',
	];
	protected array $relationshipInfo = [
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => Connector::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => Connector::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'wp_post' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => WpPost::class,
			'foreignKeyColumnName' => 'wp_post_id',
			'foreignKey' => Connector::FIELD_WP_POST_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => WpPost::FIELD_ID,
			'ownerKeyColumnName' => 'wp_post_id',
			'ownerKey' => Connector::FIELD_WP_POST_ID,
			'methodName' => 'wp_post',
		],
		'connections' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Connection::class,
			'foreignKey' => Connection::FIELD_CONNECTOR_ID,
			'localKey' => Connection::FIELD_ID,
			'methodName' => 'connections',
		],
		'connector_imports' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => ConnectorImport::class,
			'foreignKey' => ConnectorImport::FIELD_CONNECTOR_ID,
			'localKey' => ConnectorImport::FIELD_ID,
			'methodName' => 'connector_imports',
		],
		'connector_requests' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => ConnectorRequest::class,
			'foreignKey' => ConnectorRequest::FIELD_CONNECTOR_ID,
			'localKey' => ConnectorRequest::FIELD_ID,
			'methodName' => 'connector_requests',
		],
		'measurements' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Measurement::class,
			'foreignKey' => Measurement::FIELD_CONNECTOR_ID,
			'localKey' => Measurement::FIELD_ID,
			'methodName' => 'measurements',
		],
	];
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, Connector::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			Connector::FIELD_CLIENT_ID);
	}
	public function wp_post(): BelongsTo{
		return $this->belongsTo(WpPost::class, Connector::FIELD_WP_POST_ID, WpPost::FIELD_ID,
			Connector::FIELD_WP_POST_ID);
	}
	public function connections(): HasMany{
		return $this->hasMany(Connection::class, Connection::FIELD_CONNECTOR_ID, static::FIELD_ID);
	}
	public function connector_imports(): HasMany{
		return $this->hasMany(ConnectorImport::class, ConnectorImport::FIELD_CONNECTOR_ID, static::FIELD_ID);
	}
	public function connector_requests(): HasMany{
		return $this->hasMany(ConnectorRequest::class, ConnectorRequest::FIELD_CONNECTOR_ID, static::FIELD_ID);
	}
	public function measurements(): HasMany{
		return $this->hasMany(Measurement::class, Measurement::FIELD_CONNECTOR_ID, static::FIELD_ID);
	}
}
