<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Exceptions\ModelValidationException;
use App\Properties\OAClient\OAClientClientSecretProperty;
use App\Traits\HardCodable;
use App\Traits\HasJsonFile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Buttons\QMButton;
use App\Buttons\States\ImportStateButton;
use App\DataSources\QMConnector;
use App\DataSources\QMDataSource;
use App\Models\Base\BaseConnector;
use App\Properties\User\UserIdProperty;
use App\Slim\Model\DBModel;
use App\Storage\S3\S3Public;
use App\Traits\HasDBModel;
use App\Traits\ModelTraits\ConnectorTrait;
use App\Types\QMStr;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\UI\QMColor;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\Fields\Avatar;
use App\Fields\ID;
use App\Fields\Text;
use MathieuTu\JsonSyncer\Contracts\JsonExportable;
use MathieuTu\JsonSyncer\Contracts\JsonImportable;
use MathieuTu\JsonSyncer\Traits\JsonExporter;
use MathieuTu\JsonSyncer\Traits\JsonImporter;

/**
 * App\Models\Connector
 * @OA\Schema (
 *      definition="Connector",
 *      required={"name", "display_name", "image", "get_it_url", "short_description", "long_description", "enabled",
 *     "oauth"},
 *      @OA\Property(
 *          property="id",
 *          description="Connector ID number",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="name",
 *          description="Lowercase system name for the data source",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="display_name",
 *          description="Pretty display name for the data source",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="image",
 *          description="URL to the image of the connector logo",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="get_it_url",
 *          description="URL to a site where one can get this device or application",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="short_description",
 *          description="Short description of the service (such as the categories it tracks)",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="long_description",
 *          description="Longer paragraph description of the data provider",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="enabled",
 *          description="Set to 1 if the connector should be returned when listing connectors",
 *          type="boolean"
 *      ),
 *      @OA\Property(
 *          property="oauth",
 *          description="Set to 1 if the connector uses OAuth authentication as opposed to username/password",
 *          type="boolean"
 *      ),
 *      @OA\Property(
 *          property="created_at",
 *          description="When the record was first created. Use ISO 8601 datetime format",
 *          type="string",
 *          format="date-time"
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          description="When the record in the database was last updated. Use ISO 8601 datetime format",
 *          type="string",
 *          format="date-time"
 *      ),
 * )
 * @property integer $id Connector ID number
 * @property string $name Connector lowercase system name
 * @property string $display_name Connector pretty display name
 * @property string $image URL to the image of the connector logo
 * @property string $get_it_url URL to a site where one can get this device or application
 * @property string $short_description Short description
 * @property string $long_description Long description
 * @property boolean $enabled
 * @property boolean $oauth
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|Connector whereId($value)
 * @method static \Illuminate\Database\Query\Builder|Connector whereName($value)
 * @method static \Illuminate\Database\Query\Builder|Connector whereDisplayName($value)
 * @method static \Illuminate\Database\Query\Builder|Connector whereImage($value)
 * @method static \Illuminate\Database\Query\Builder|Connector whereGetItUrl($value)
 * @method static \Illuminate\Database\Query\Builder|Connector whereShortDescription($value)
 * @method static \Illuminate\Database\Query\Builder|Connector whereLongDescription($value)
 * @method static \Illuminate\Database\Query\Builder|Connector whereEnabled($value)
 * @method static \Illuminate\Database\Query\Builder|Connector whereOauth($value)
 * @method static \Illuminate\Database\Query\Builder|Connector whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Connector whereUpdatedAt($value)
 * @property int|null $qm_client Whether its a connector or one of our clients
 * @property string|null $client_id
 * @property string|null $deleted_at
 * @method static Builder|Connector newModelQuery()
 * @method static Builder|Connector newQuery()
 * @method static Builder|Connector query()
 * @method static Builder|Connector whereClientId($value)
 * @method static Builder|Connector whereDeletedAt($value)
 * @method static Builder|Connector whereIsParent($value)
 * @method static Builder|Connector whereQmClient($value)
 * @method static Builder|Connector withTrashed()
 * @mixin Eloquent
 * @property-read OAClient|null $oa_client
 * @property-read Collection|Connection[] $connections
 * @property-read int|null $connections_count
 * @property-read Collection|Measurement[] $measurements
 * @property-read int|null $measurements_count
 * @property-read Collection|ConnectorImport[] $connector_imports
 * @property-read int|null $connector_imports_count
 * @property int|null $wp_post_id
 * @method static Builder|Connector whereWpPostId($value)
 * @property-read WpPost $wp_post
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @property int|null $number_of_connections Number of Connections for this Connector.
 *                 [Formula:
 *                     update connectors
 *                         left join (
 *                             select count(id) as total, connector_id
 *                             from connections
 *                             group by connector_id
 *                         )
 *                         as grouped on connectors.id = grouped.connector_id
 *                     set connectors.number_of_connections = count(grouped.total)
 *                 ]
 * @property int|null $number_of_connector_imports Number of Connector Imports for this Connector.
 *                 [Formula:
 *                     update connectors
 *                         left join (
 *                             select count(id) as total, connector_id
 *                             from connector_imports
 *                             group by connector_id
 *                         )
 *                         as grouped on connectors.id = grouped.connector_id
 *                     set connectors.number_of_connector_imports = count(grouped.total)
 *                 ]
 * @property int|null $number_of_connector_requests Number of Connector Requests for this Connector.
 *                 [Formula:
 *                     update connectors
 *                         left join (
 *                             select count(id) as total, connector_id
 *                             from connector_requests
 *                             group by connector_id
 *                         )
 *                         as grouped on connectors.id = grouped.connector_id
 *                     set connectors.number_of_connector_requests = count(grouped.total)
 *                 ]
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|Connector whereNumberOfConnections($value)
 * @method static Builder|Connector whereNumberOfConnectorImports($value)
 * @method static Builder|Connector whereNumberOfConnectorRequests($value)
 * @property-read Collection|ConnectorRequest[] $connector_requests
 * @property-read int|null $connector_requests_count
 * @property int|null $number_of_measurements Number of Measurements for this Connector.
 *                     [Formula: update connectors
 *                         left join (
 *                             select count(id) as total, connector_id
 *                             from measurements
 *                             group by connector_id
 *                         )
 *                         as grouped on connectors.id = grouped.connector_id
 *                     set connectors.number_of_measurements = count(grouped.total)]
 * @method static Builder|Connector whereNumberOfMeasurements($value)
 * @property bool|null $is_public
 * @property int $sort_order
 * @method static Builder|Connector whereIsPublic($value)
 * @method static Builder|Connector whereSortOrder($value)
 * @property string|null $slug The slug is the part of a URL that identifies a page in human-readable keywords.
 * @property-read OAClient|null $client
 * @method static Builder|Connector whereSlug($value)
 */
class Connector extends BaseConnector implements JsonExportable, JsonImportable {
    use HasFactory;
    use HardCodable;
	use ConnectorTrait;
	use HasDBModel;
    use JsonExporter;
    use JsonImporter;
    use HasJsonFile;
    public const DEPRECATED_PROPERTIES = [
        self::FIELD_QM_CLIENT,
        self::FIELD_WP_POST_ID,
    ];
	public static function getSlimClass(): string{ return QMConnector::class; }
	const CLASS_CATEGORY = Connection::CLASS_CATEGORY;
	public const CLASS_DESCRIPTION = "A connector pulls data from other data providers using their API or a screenscraper. Returns a list of all available connectors and information about them such as their id, name, whether the user has provided access, logo url, connection instructions, and the update history.";
	public const COLOR = QMColor::HEX_GREEN;
	public const METABASE_PATH = ConnectorImport::METABASE_PATH;
	public const FONT_AWESOME = FontAwesome::CLOUD_DOWNLOAD_ALT_SOLID;
	public const DEFAULT_SEARCH_FIELD = Connector::FIELD_DISPLAY_NAME;
	public const DEFAULT_ORDERINGS = [self::FIELD_NUMBER_OF_CONNECTIONS => self::ORDER_DIRECTION_DESC];
	public const DEFAULT_LIMIT = 50;
	public const DEFAULT_IMAGE = ImageUrls::ESSENTIAL_COLLECTION_DOWNLOAD;
	public $table = self::TABLE;
	/**
	 * The attributes that should be casted to native types.
	 * @var array
	 */
	protected $casts = [
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_DISPLAY_NAME => 'string',
		self::FIELD_ENABLED => 'bool',
		self::FIELD_GET_IT_URL => 'string',
		self::FIELD_ID => 'int',
		self::FIELD_IMAGE => 'string',
		self::FIELD_LONG_DESCRIPTION => 'string',
		self::FIELD_NAME => 'string',
		self::FIELD_NUMBER_OF_CONNECTIONS => 'int',
		self::FIELD_NUMBER_OF_CONNECTOR_IMPORTS => 'int',
		self::FIELD_NUMBER_OF_CONNECTOR_REQUESTS => 'int',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'int',
		self::FIELD_OAUTH => 'bool',
		self::FIELD_QM_CLIENT => 'bool',
		self::FIELD_SHORT_DESCRIPTION => 'string',
		self::FIELD_WP_POST_ID => 'int',
	];
	/**
	 * @return string
	 */
	public function getLogMetaDataString(): string{
		return $this->display_name . " ";
	}

    /**
     * @return OAClient
     * @throws ModelValidationException
     */
	public function getOrCreateClient(): OAClient{
		$clientId = trim($this->name);
		$client = OAClient::getOrCreate($clientId, [OAClient::FIELD_USER_ID => UserIdProperty::USER_ID_SYSTEM]);
		if($this->client_id !== $client->client_id){
			$this->client_id = $client->client_id;
			$this->save();
		}
		return $client;
	}
	protected function getFilePath(): string{
		return 'app/DataSources/Connectors/' . QMStr::toClassName($this->display_name) . '.php';
	}
    public function getJsonExportableAttributes(): array
    {
        return $this->jsonExportableAttributes = array_keys($this->attributesToArray());
    }
    public function getJsonExportableRelations(): array{
        return $this->jsonExportableRelations = [];
    }
	public function getPHPUnitTestUrl(): string{
		return $this->getDBModel()->getPHPUnitTestUrl();
	}
	public function getImageLink(array $params = [], string $style = null): string{
		if(!$style){
			$style = "height: 50px; cursor: pointer;";
		}
		return parent::getDataLabImageLink($params, $style);
	}
	public function getEditUrl(array $params = []): string{
		$params[Connection::FIELD_CONNECTOR_ID] = $this->getId();
		return ImportStateButton::make()->getUrl($params);
	}
	/**
	 * @return QMDataSource
	 */
	public function getDBModel(): DBModel{
		return QMDataSource::find($this->id);
	}
	/**
	 * @return QMButton[]
	 */
	public function getActionButtons(): array{
		return $this->getQMConnector()->getButtons();
	}
	/**
	 * @return QMConnector|DBModel|QMDataSource
	 */
	public function getQMConnector(): QMConnector{
		return $this->getDBModel();
	}
	/**
	 * @param Builder|\Illuminate\Database\Query\Builder $qb
	 * @param null $user
	 * @return \Illuminate\Database\Query\Builder
	 */
	public function restrictQueryBasedOnPermissions($qb, $user = null): \Illuminate\Database\Query\Builder{
		$qb->where(self::FIELD_ENABLED, true);
		return parent::restrictQueryBasedOnPermissions($qb, $user);
	}
	/**
	 * @param null $reader
	 * @return bool
	 */
	public function canReadMe($reader = null): bool{
		return true; // I guess we should keep showing disabled ones if they used to have a connection?  Plus it
		//if($this->enabled){return true;}
		//return parent::hasReadAccess($accessor);
	}
	public function getImage(): string{
		if($this->attributes){
			$img = $this->attributes[self::FIELD_IMAGE];
		} else{
			$img = static::DEFAULT_IMAGE;
		}
		return $img;
	}
	/**
	 * Determine if this resource is available for navigation.
	 * @param Request $request
	 * @return bool
	 */
	public static function availableForNavigation(Request $request): bool{
		return true;
	}
	/**
	 * Get the fields displayed by the resource.
	 * @param Request $request
	 * @return array
	 */
	public function getFields(): array{
		return [
			Avatar::make(str_repeat(' ', 8), function(){
				return $this->getImage();
			})->disk(S3Public::DISK_NAME)->path('images/' . Connector::TABLE)->maxWidth(50)->disableDownload()
				->squared()->thumbnail(function(){
                    return $this->getImage();
				})->preview(function(){
                    return $this->getImage();
				}),
			Text::make('Name', Connector::FIELD_NAME, function(){
                return $this->getTitleAttribute();
			})->sortable()->readonly()->detailLink()->rules('required'),
			//MeasurementResource::hasMany(),
			ID::make()->hideFromIndex()->readonly()->sortable(),
		];
	}
    public function save(array $options = []): bool
    {
        $client = OAClient::findInMemoryOrDB($this->name);
        if(!$client){
            $client = new OAClient();
            $client->client_id = $this->name;
            $client->user_id = UserIdProperty::USER_ID_SYSTEM;
            $client->client_secret = OAClientClientSecretProperty::generate();
            $client->save();
        }
        return parent::save($options);
    }

    /**
     * @param $models
     * @return \Illuminate\Support\Collection|static[]
     */
    protected static function sortByDefaultOrdering($models): \Illuminate\Support\Collection|static
    {
        $models = parent::sortByDefaultOrdering($models);
        $models = $models->sortByDesc(function(self $model){
            if($model->sort_order){
                return $model->sort_order;
            }
            return $model->sort_order = $model->number_of_measurements ?? $model->number_of_connections ??
                $model->number_of_connector_requests ??
                $model->id;
        });
        return $models;
    }
	public static function generateServicesPhpConfig(){
		$all = QMConnector::all();
		$services = '';
		foreach($all as $connector){
			$upper = strtoupper($connector->name);
			$callback = $connector->getCallbackUrl();
			
			$services .= "
	'$connector->name' => [
	    'client_id' => env('CONNECTOR_{$upper}_CLIENT_ID'),
	    'client_secret' => env('CONNECTOR_{$upper}_CLIENT_SECRET'),
	    'redirect' => env('CONNECTOR_{$upper}_CLIENT_SECRET'),
	],
";
		}
	}

}
