<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Buttons\RelationshipButtons\ConnectorImport\ConnectorImportConnectionButton;
use App\Buttons\RelationshipButtons\ConnectorImport\ConnectorImportConnectorButton;
use App\Buttons\RelationshipButtons\ConnectorImport\ConnectorImportMeasurementsButton;
use App\Buttons\RelationshipButtons\ConnectorImport\ConnectorImportUserButton;
use App\Buttons\RelationshipButtons\RelationshipButton;
use App\Logging\QMClockwork;
use App\Models\Base\BaseConnectorImport;
use App\Traits\HasErrors;
use App\Traits\HasModel\HasDataSource;
use App\Traits\HasModel\HasImporterConnection;
use App\Traits\HasModel\HasUser;
use App\Traits\TestableTrait;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\UI\QMColor;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Titasgailius\SearchRelations\SearchesRelations;
/**
 * App\Models\ConnectorImport
 * @OA\Schema (
 *      definition="Update",
 *      required={"user_id", "connector_id", "number_of_measurements", "success", "message"},
 *      @OA\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="user_id",
 *          description="user_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="connector_id",
 *          description="connector_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="number_of_measurements",
 *          description="number_of_measurements",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="success",
 *          description="success",
 *          type="boolean"
 *      ),
 *      @OA\Property(
 *          property="message",
 *          description="message",
 *          type="string"
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
 *      )
 * )
 * @property integer $id
 * @property integer $user_id
 * @property integer $connector_id
 * @property integer $number_of_measurements
 * @property boolean $success
 * @property string $message
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|ConnectorImport whereId($value)
 * @method static \Illuminate\Database\Query\Builder|ConnectorImport whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|ConnectorImport whereConnectorId($value)
 * @method static \Illuminate\Database\Query\Builder|ConnectorImport whereNumberOfMeasurements($value)
 * @method static \Illuminate\Database\Query\Builder|ConnectorImport whereSuccess($value)
 * @method static \Illuminate\Database\Query\Builder|ConnectorImport whereMessage($value)
 * @method static \Illuminate\Database\Query\Builder|ConnectorImport whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|ConnectorImport whereUpdatedAt($value)
 * @property-read Connector $connector
 * @property string|null $deleted_at
 * @property string|null $client_id
 * @method static Builder|ConnectorImport newModelQuery()
 * @method static Builder|ConnectorImport newQuery()
 * @method static Builder|ConnectorImport query()
 * @method static Builder|ConnectorImport whereClientId($value)
 * @method static Builder|ConnectorImport whereDeletedAt($value)
 * @mixin Eloquent
 * @property-read OAClient|null $oa_client
 * @property-read User $user
 * @property int|null $connection_id
 * @property \Illuminate\Support\Carbon $earliest_measurement_at
 * @property \Illuminate\Support\Carbon|null $import_ended_at
 * @property \Illuminate\Support\Carbon|null $import_started_at
 * @property string|null $internal_error_message
 * @property \Illuminate\Support\Carbon $latest_measurement_at
 * @property string|null $reason_for_import
 * @property string|null $user_error_message
 * @property array|null $additional_meta_data
 * @method static Builder|ConnectorImport whereAdditionalMetaData($value)
 * @method static Builder|ConnectorImport whereConnectionId($value)
 * @method static Builder|ConnectorImport whereEarliestMeasurementAt($value)
 * @method static Builder|ConnectorImport whereImportEndedAt($value)
 * @method static Builder|ConnectorImport whereImportStartedAt($value)
 * @method static Builder|ConnectorImport whereInternalErrorMessage($value)
 * @method static Builder|ConnectorImport whereLatestMeasurementAt($value)
 * @method static Builder|ConnectorImport whereReasonForImport($value)
 * @method static Builder|ConnectorImport whereUserErrorMessage($value)
 * @property-read Connection|null $connection
 * @property-read Collection|Measurement[] $measurements
 * @property-read int|null $measurements_count
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @property int|null $number_of_connector_requests Number of Connector Requests for this Connector Import.
 *                 [Formula:
 *                     update connector_imports
 *                         left join (
 *                             select count(id) as total, connector_import_id
 *                             from connector_requests
 *                             group by connector_import_id
 *                         )
 *                         as grouped on connector_imports.id = grouped.connector_import_id
 *                     set connector_imports.number_of_connector_requests = count(grouped.total)
 *                 ]
 * @property string|null $imported_data_from_at Earliest data that we've requested from this data source
 * @property string|null $imported_data_end_at Most recent data that we've requested from this data source
 * @property string|null $credentials Encrypted user credentials for accessing third party data
 * @property string|null $connector_requests Most recent data that we've requested from this data source
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|ConnectorImport whereConnectorRequests($value)
 * @method static Builder|ConnectorImport whereCredentials($value)
 * @method static Builder|ConnectorImport whereImportedDataEndAt($value)
 * @method static Builder|ConnectorImport whereImportedDataFromAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ConnectorImport
 *     whereNumberOfConnectorRequests($value)
 * @property-read int|null $connector_requests_count
 * @property-read OAClient|null $client
 */
class ConnectorImport extends BaseConnectorImport {
    use HasFactory;

	use HasUser, HasDataSource, HasImporterConnection, HasErrors, TestableTrait;
	use SearchesRelations;
	/**
	 * The single value that should be used to represent the resource when being displayed.
	 * @var string
	 */
	public static $title = Connector::FIELD_DISPLAY_NAME;
	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [];
	public static $group = Connection::CLASS_CATEGORY;
	/**
	 * The number of results to display in the global search.
	 * @var int
	 */
	public static $globalSearchResults = 10;
	/**
	 * The relationship columns that should be searched.
	 * @var array
	 */
	public static $searchRelations = [
		'connector' => ['name'],
	];
	/**
	 * The per-page options used the resource index.
	 * @var array
	 */
	public static $perPageOptions = [10, 25, 50, 100];
	public const METABASE_PATH = '/dashboard/5-connector-imports';
	const CLASS_CATEGORY = Connection::CLASS_CATEGORY;
	public const CLASS_DESCRIPTION = "A record of attempts to import from a given data source. ";
	public const COLOR = QMColor::HEX_GREEN;
	public const FONT_AWESOME = FontAwesome::CLOUD_DOWNLOAD_ALT_SOLID;
	public const DEFAULT_SEARCH_FIELD = Connector::FIELD_DISPLAY_NAME;
	public const DEFAULT_LIMIT = 20;
	public const DEFAULT_IMAGE = ImageUrls::ESSENTIAL_COLLECTION_DOWNLOAD;
	/**
	 * @var array The relationships that should always be loaded.
	 */
	protected $with = [
		//        'user',
		//        'connector',
		//        'connection'
	];
	protected array $rules = [
		self::FIELD_CLIENT_ID => 'nullable|max:80',
		self::FIELD_CONNECTION_ID => 'nullable|integer|min:1|max:2147483647',
		self::FIELD_CONNECTOR_ID => 'required|integer|min:1|max:2147483647',
		self::FIELD_EARLIEST_MEASUREMENT_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_IMPORT_ENDED_AT => 'required|date',
		self::FIELD_IMPORT_STARTED_AT => 'required|date',
		self::FIELD_INTERNAL_ERROR_MESSAGE => 'nullable|max:1000',
		self::FIELD_LATEST_MEASUREMENT_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'required|integer|min:0|max:2147483647',
		self::FIELD_REASON_FOR_IMPORT => 'nullable|max:255',
		self::FIELD_SUCCESS => 'nullable|boolean',
		self::FIELD_USER_ERROR_MESSAGE => 'nullable|max:500',
		self::FIELD_USER_ID => 'required|numeric|min:1',
	];
	/**
	 * @param array|null $meta
	 * @return array
	 */
	public function getLogMetaData(?array $meta = []): array{
		if($this->import_started_at){
			$meta["Started"] = $this->import_started_at->format(TimeHelper::MONTH_DAY_HOUR_MINUTE);
		}
		$meta['Connector'] = $this->connector->display_name;
		$meta["Measurements"] = $this->number_of_measurements;
		$meta[self::FIELD_SUCCESS] = $this->success;
		$meta["Ended"] =
			($this->import_ended_at) ? $this->import_ended_at->format(TimeHelper::MONTH_DAY_HOUR_MINUTE) : null;
		$meta["Error"] = $this->internal_error_message;
		if($this->user && $this->user->display_name){
			$meta['User'] = QMStr::truncate($this->user->display_name, 20);
		}
		if($this->id){
			$meta['SHOW'] = $this->getUrl();
		}
		return $meta;
	}
	public function getNameAttribute(): string{
		return $this->connector->display_name . " for " . $this->user->display_name;
	}
	public function getNumberOfMeasurementsLink(array $params = []): string{
		$params[Measurement::FIELD_CONNECTION_ID] = $this->id;
		$url = Measurement::generateDataLabIndexUrl($params);
		$name = $this->number_of_measurements;
		if($name === null){
			$name = "N/A";
		}
		return "<a href=\"$url\" target='_blank' title=\"See Measurements\">$name</a>";
	}
	public function getSubtitleAttribute(): string{
		if(!$this->hasId()){
			return static::CLASS_DESCRIPTION;
		}
		return $this->getTitleAttribute() . " from " . $this->getTimeSinceUpdated();
	}
	public function getTitleAttribute(): string{
		if(!$this->hasId()){
			return static::getClassNameTitle();
		}
		return $this->getQMConnector()->getTitleAttribute() . " import for " . $this->getUser()->getTitleAttribute();
	}
	/** @noinspection PhpUnused */
	/**
	 * @param $value
	 */
	public function setInternalErrorMessageAttribute($value){
		if($value){
			if($this->hasId()){ // Don't log errors when populating from database in the constructor before id is set
				$this->logError($value);
			}
			if(stripos($value, 'message":')){
				$value = QMStr::after('message":"', $value, $value);
				$value = QMStr::before('","type', $value, $value);
			}
			$value = QMStr::truncate($value, '245');
		}
		$this->attributes[self::FIELD_INTERNAL_ERROR_MESSAGE] = $value;
	}
	public function test(): void{
		$this->getImporterConnection()->test();
	}
	/**
	 * @return RelationshipButton[]
	 */
	public function getInterestingRelationshipButtons(): array{
		return [
			new ConnectorImportConnectorButton($this),
			new ConnectorImportConnectionButton($this),
			new ConnectorImportUserButton($this),
			new ConnectorImportMeasurementsButton($this),
		];
	}
	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_USER_ID];
	}
	/**
	 * Determine if this resource is available for navigation.
	 * @param Request $request
	 * @return bool
	 */
	public static function availableForNavigation(Request $request): bool{ return true; }
	public function save(array $options = []): bool{
		if($end = $this->import_ended_at){
			$connector = $this->getQMConnector();
			QMClockwork::logDuration("$connector Import", $this->import_started_at, $end);
		}
		return parent::save($options); // TODO: Change the autogenerated stub
	}
}
