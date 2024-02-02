<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Casts\OAuthAuthenticatorCast;
use App\DataSources\Connectors\Exceptions\ConnectorDisabledException;
use App\Exceptions\NoGeoDataException;
use App\Http\Controllers\ApiConnectorController;
use App\Logging\ConsoleLog;
use App\Properties\Connection\ConnectionUserErrorMessageProperty;
use App\Traits\HasMany\HasManyMeasurements;
use Carbon\CarbonInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Buttons\QMButton;
use App\Buttons\RelationshipButtons\Connection\ConnectionConnectorButton;
use App\Buttons\RelationshipButtons\Connection\ConnectionMeasurementsButton;
use App\Buttons\RelationshipButtons\Connection\ConnectionUserButton;
use App\Buttons\RelationshipButtons\RelationshipButton;
use App\Buttons\States\HistoryAllStateButton;
use App\Buttons\States\ImportStateButton;
use App\DataSources\Connectors\Exceptions\RecentImportException;
use App\DataSources\CredentialStorage;
use App\DataSources\QMConnector;
use App\DataSources\QMDataSource;
use App\Exceptions\CredentialsNotFoundException;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\QMException;
use App\Exceptions\TemporaryImportException;
use App\Exceptions\TooSlowException;
use App\Logging\QMLog;
use App\Models\Base\BaseConnection;
use App\Astral\Actions\ImportAction;
use App\Astral\Actions\PHPUnitAction;
use App\Astral\MeasurementBaseAstralResource;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Connection\ConnectionConnectStatusProperty;
use App\Properties\Connection\ConnectionLatestMeasurementAtProperty;
use App\Properties\Connection\ConnectionNumberOfMeasurementsProperty;
use App\Properties\Connection\ConnectionUpdateStatusProperty;
use App\Slim\Controller\Connector\ConnectorException;
use App\Slim\Controller\Connector\ConnectorResponse;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\DBModel;
use App\Slim\Model\GoogleAnalyticsEvent;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\User\QMUser;
use App\Storage\DB\QMQB;
use App\Tables\QMTable;
use App\Traits\HasErrors;
use App\Traits\HasModel\HasDataSource;
use App\Traits\HasModel\HasUser;
use App\Traits\ImportableTrait;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\UI\QMColor;
use App\Utils\AppMode;
use App\Utils\IonicHelper;
use App\Utils\SecretHelper;
use App\Variables\QMUserVariable;
use Carbon\Carbon;
use Eloquence\Behaviours\CamelCasing;
use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use App\Actions\ActionEvent;
use App\Fields\Field;
use App\Http\Requests\AstralRequest;
use LogicException;
use OAuth\Common\Storage\Exception\TokenNotFoundException;
use PHPUnit\Framework\TestFailure;
use stdClass;
use Tests\TestGenerators\StagingJobTestFile;
use Throwable;
use Titasgailius\SearchRelations\SearchesRelations;
/**
 * Class BaseConnection
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
 * @property string $deleted_at
 * @property int $total_measurements_in_last_update
 * @property string $user_message
 * @property Carbon $latest_measurement_at
 * @property Carbon $import_started_at
 * @property Carbon $import_ended_at
 * @property string $reason_for_import
 * @property string $user_error_message
 * @property string $internal_error_message
 * @property Connector $connector
 * @property User $user
 * @package App\Models\Base
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection newQuery()
 * @method static Builder|BaseConnection onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereConnectError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereConnectStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereConnectorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereImportEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereImportStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereInternalErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereLastSuccessfulUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereLatestMeasurementAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereReasonForImport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereTotalMeasurementsInLastUpdate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereUpdateError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereUpdateRequestedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereUpdateStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereUserErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseConnection whereUserMessage($value)
 * @method static Builder|BaseConnection withTrashed()
 * @method static Builder|BaseConnection withoutTrashed()
 * @mixin Eloquent
 * @property-read OAClient|null $oa_client
 * @property-read Collection|ConnectorImport[] $connector_imports
 * @property-read int|null $connector_imports_count
 * @property-read Collection|Measurement[] $measurements
 * @property-read int|null $measurements_count
 * @property int|null $wp_post_id
 * @method static \Illuminate\Database\Eloquent\Builder|Connection whereWpPostId($value)
 * @property-read WpPost $wp_post
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel nPerGroup($group, $n = 10)
 * @property int|null $number_of_connector_imports Number of Connector Imports for this Connection.
 *                 [Formula:
 *                     update connections
 *                         left join (
 *                             select count(id) as total, connection_id
 *                             from connector_imports
 *                             group by connection_id
 *                         )
 *                         as grouped on connections.id = grouped.connection_id
 *                     set connections.number_of_connector_imports = count(grouped.total)
 *                 ]
 * @property int|null $number_of_connector_requests Number of Connector Requests for this Connection.
 *                 [Formula:
 *                     update connections
 *                         left join (
 *                             select count(id) as total, connection_id
 *                             from connector_requests
 *                             group by connection_id
 *                         )
 *                         as grouped on connections.id = grouped.connection_id
 *                     set connections.number_of_connector_requests = count(grouped.total)
 *                 ]
 * @property array|null $credentials Encrypted user credentials for accessing third party data
 * @property string|null $imported_data_from_at Earliest data that we've requested from this data source
 * @property string|null $imported_data_end_at Most recent data that we've requested from this data source
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|Connection whereCredentials($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Connection whereImportedDataEndAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Connection whereImportedDataFromAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Connection whereNumberOfConnectorImports($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Connection whereNumberOfConnectorRequests($value)
 * @property-read Collection|ConnectorRequest[] $connector_requests
 * @property-read int|null $connector_requests_count
 * @property int|null $number_of_measurements Number of Measurements for this Connection.
 *                     [Formula: update connections
 *                         left join (
 *                             select count(id) as total, connection_id
 *                             from measurements
 *                             group by connection_id
 *                         )
 *                         as grouped on connections.id = grouped.connection_id
 *                     set connections.number_of_measurements = count(grouped.total)]
 * @method static \Illuminate\Database\Eloquent\Builder|Connection whereNumberOfMeasurements($value)

 * @property-read Collection|ActionEvent[] $actions
 * @property-read int|null $actions_count
 * @property mixed $raw
 * @property bool|null $is_public
 * @method static \Illuminate\Database\Eloquent\Builder|Connection whereIsPublic($value)
 * @property string|null $slug The slug is the part of a URL that identifies a page in human-readable keywords.
 * @property array|null $meta Additional meta data instructions for import, such as a list of repositories the Github
 *     connector should import from.
 * @property-read OAClient|null $client
 * @property-read int|null $credentials_count
 * @method static \Illuminate\Database\Eloquent\Builder|Connection whereMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Connection whereSlug($value)
 */
class Connection extends BaseConnection {
    use HasFactory;
	use HasDataSource, HasErrors, HasUser, ImportableTrait, HasManyMeasurements;
	use SearchesRelations;
	/**
	 * The single value that should be used to represent the resource when being displayed.
	 * @var string
	 */
	public static $title = 'id';
	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [//'id',
	];
	public static $group = Connection::CLASS_CATEGORY;
	public static $searchRelations = [
		'connector' => [Connector::FIELD_DISPLAY_NAME],
	];
	public function countMeasurements(): void{
		$hasMany = $this->measurements();
		$this->number_of_measurements = $hasMany->count();
		$this->save();
	}
	/**
	 * @return \App\Models\Measurement[]|\Illuminate\Database\Eloquent\Collection
	 * @noinspection PhpDocSignatureInspection
	 */
	public function getMeasurements(): Collection{
		ConsoleLog::info(__METHOD__." for $this...");
		$this->loadMissing('measurements');
		$measurements = $this->measurements;
		return $measurements;
	}
	/**
	 * Get the searchable columns for the resource.
	 * @return array
	 */
	public static function searchableColumns(): array{
		//$parent = parent::searchableColumns();
		return []; // Prevents returning id field
	}
	public const CLASS_DESCRIPTION                        = "Connections to 3rd party data sources that we can import from.";
	public const MINIMUM_SECONDS_BETWEEN_IMPORT_ATTEMPTS  = 2 * 3600;
	public const SECONDS_BETWEEN_IMPORTS                  = 1.5 * 86400;
	public const METABASE_PATH = ConnectorImport::METABASE_PATH;
	public const FONT_AWESOME                             = FontAwesome::PLUG_SOLID;
	public const COLOR                                    = QMColor::HEX_GREEN;
	public static function getSlimClass(): string{ return Connection::class; }
	public const    DEFAULT_SEARCH_FIELD                      = 'connector.' . Connector::FIELD_DISPLAY_NAME;
	public const    DEFAULT_ORDER_DIRECTION                   = 'asc';
	public const    DEFAULT_LIMIT                             = 20;
	public const    DEFAULT_IMAGE                             = ImageUrls::ESSENTIAL_COLLECTION_DOWNLOAD;
	public const    ERROR_FIELDS                              = [
		Connection::FIELD_CONNECT_ERROR,
		Connection::FIELD_UPDATE_ERROR,
		Connection::FIELD_INTERNAL_ERROR_MESSAGE,
		Connection::FIELD_USER_ERROR_MESSAGE,
	];
	protected const TEMPORARY_IMPORT_ISSUE_USER_ERROR_MESSAGE = "We had a temporary problem importing your data.  I'll try again later. ";
	protected const TRY_RECONNECTING_USER_ERROR_MESSAGE       = "We got an unauthorized response when trying to import. Please try reconnecting. ";
	public static function getUniqueIndexColumns(): array{
		return [
			self::FIELD_USER_ID,
			self::FIELD_CONNECTOR_ID,
		];
	}
	const CLASS_CATEGORY = "Data Sources";
	/**
	 * @var array The relationships that should always be loaded.
	 */
	protected $with = [
		//'user',
		//'connector'
	];
	protected array $rules = [
		self::FIELD_CLIENT_ID => 'nullable|max:80',
		self::FIELD_USER_ID => 'required|numeric|min:1',
		self::FIELD_CONNECTOR_ID => 'required|integer|min:1|max:2147483647',
		self::FIELD_CONNECT_STATUS => 'required|max:32',
		self::FIELD_CONNECT_ERROR => 'nullable|max:65535',
		self::FIELD_UPDATE_REQUESTED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_UPDATE_STATUS => 'required|max:32',
		self::FIELD_UPDATE_ERROR => 'nullable|max:65535',
		self::FIELD_LAST_SUCCESSFUL_UPDATED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_TOTAL_MEASUREMENTS_IN_LAST_UPDATE => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_USER_MESSAGE => 'nullable|max:255',
		self::FIELD_LATEST_MEASUREMENT_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_IMPORT_STARTED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_IMPORT_ENDED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_REASON_FOR_IMPORT => 'nullable|max:255',
		self::FIELD_USER_ERROR_MESSAGE => 'nullable|max:500',
		self::FIELD_INTERNAL_ERROR_MESSAGE => 'nullable|max:1000',
		self::FIELD_WP_POST_ID => 'nullable|numeric|min:1',
	];
	public static function logSuccessfulImports(): void{
		$connections = Connection::where(Connection::FIELD_IMPORT_ENDED_AT, ">", now()->subDay())->get();
		QMTable::collectionToConsoleTable($connections);
	}
	/**
	 * @return string
	 */
	public function getLogMetaDataString(): string{
		return $this->getQMConnector()->displayName . " for " . $this->getUser()->display_name;
	}
	/**
	 * @param array|null $meta
	 * @return array
	 */
	public function getLogMetaData(?array $meta = []): array{
		$time = null;
		if($this->import_ended_at){
			$time = $this->import_ended_at->format(TimeHelper::MONTH_DAY_HOUR_MINUTE);
		}
		$meta["Imported At"] = $time;
		$meta["Connector"] = QMConnector::idToName($this->connector_id);
		$meta["Measurements"] = $this->total_measurements_in_last_update;
		if($u = $this->getUserFromMemory()){
			$meta["User"] = $u->display_name;
		}
		if($this->hasValidId()){
			$meta['EDIT'] = $this->getEditUrl();
			$meta['SHOW'] = $this->getUrl();
		}
		return $meta;
	}
	/**
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public static function whereErrored(): \Illuminate\Database\Eloquent\Builder{
		$qb = self::query()->where(self::FIELD_IMPORT_STARTED_AT, "<", Carbon::now()->subDay())
			->where(self::FIELD_CONNECT_STATUS, ConnectionConnectStatusProperty::CONNECT_STATUS_CONNECTED)
			->where(Connection::FIELD_UPDATE_STATUS, ConnectionUpdateStatusProperty::IMPORT_STATUS_IMPORTING)
			->where(Connection::FIELD_USER_ID, '<>', 18535);
		QMUser::excludeTestAndDeletedUsers($qb, Connection::TABLE);
		return $qb;
	}
	public function getNameAttribute(): string{
		if(!$this->getAttribute(self::FIELD_CONNECTOR_ID)){
			return static::getClassNameTitle();
		}
		$name = QMConnector::find($this->connector_id)->getDisplayNameAttribute();
		if(AppMode::isApiRequest()){
			if(!QMAuth::canSeeOtherUsers()){
				return $name;
			}
			if(AstralRequest::isStandardIndex() && !AstralRequest::filterIsEveryone()){
				return $name;
			}
		}
		return "$name for " . $this->getUser()->getTitleAttribute();
	}
	public function getMeasurementsInLastUpdateLink(array $params = []): string{
		$params[Measurement::FIELD_CONNECTION_ID] = $this->id;
		$url = Measurement::generateDataLabIndexUrl($params);
		$name = $this->total_measurements_in_last_update;
		if($name === null){
			$name = "N/A";
		}
		return "<a href=\"$url\" target='_blank' title=\"See Measurements\">$name</a>";
	}
	/**
	 * @return string
	 */
	public function getPHPUnitTestUrl(): string{
		$shortName = (new \ReflectionClass(static::class))->getShortName();
		$id = $this->getId();
		$functions = "\$l = $shortName::find($id);" . PHP_EOL;
		$functions .= "\t\t\$l->import(__FUNCTION__);";
		return StagingJobTestFile::getUrl($shortName, $functions, static::class);
	}
	public function getSubtitleAttribute(): string{
		if(!$this->hasId()){
			return static::CLASS_DESCRIPTION;
		}
		return $this->total_measurements_in_last_update . " new measurements imported " .
			TimeHelper::timeSinceHumanString($this->import_ended_at);
	}
	public function getDataSourceId(): int{
		return $this->connector_id;
	}
	public function getEditUrl(array $params = []): string{
		$params[self::FIELD_CONNECTOR_ID] = $this->connector_id;
		return ImportStateButton::make()->getUrl($params);
	}
	public function getMeasurementsByDate(): array{
		/** @var Measurement[] $measurements */
		$measurements = $this->measurements()->get();
		$byDate = [];
		foreach($measurements as $m){
			$byDate[$m->getDate()][] = $m;
		}
		return $byDate;
	}

    /**
     * @return string
     */
    public function getConnectorName(): string{
	    $QMConnector = $this->getQMConnector();
	    return $QMConnector->getTitleAttribute();
	}
	/**
	 * @param $value
	 */
	public function setInternalErrorMessageAttribute($value){
		$prev = $this->attributes[self::FIELD_INTERNAL_ERROR_MESSAGE] ?? null;
		if($this->hasId() && $value && $value !== $prev){
			// Don't log errors when populating from database in the constructor before id is set
			$this->logError($value);
		}
		$this->attributes[self::FIELD_INTERNAL_ERROR_MESSAGE] = $value;
	}
	/**
	 * @return Field[]
	 */
	public function getFields(): array{
		$fields = parent::getFields();
		$fields[] = MeasurementBaseAstralResource::hasMany();
		return $fields;
	}
	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_USER_ID] ?? null;
	}
	public function getConnectorId(): int{
		return $this->attributes[self::FIELD_CONNECTOR_ID];
	}
	public function isStuck(): bool{
		$start = $this->import_started_at;
		return $this->update_status === ConnectionUpdateStatusProperty::IMPORT_STATUS_IMPORTING &&
			strtotime($start) < (time() - 3600);
	}
	/**
	 * Determine if this resource is available for navigation.
	 * @param Request $request
	 * @return bool
	 */
	public static function availableForNavigation(Request $request): bool{ return true; }
	/**
	 * Get the actions available for the resource.
	 * @param Request $request
	 * @return array
	 */
	public function actions(Request $request): array{
		return [
			new ImportAction($request),
			new PHPUnitAction($request),
		];
	}
	/**
	 * @return QMDataSource
	 */
	public function getQMDataSource(): ?QMDataSource{
		return $this->getQMConnector();
	}
	/**
	 * @param Throwable $e
	 * @return void
	 * @deprecated Breaks casting function
	 */
	private function setException(Throwable $e){
        $this->appends['exception'] = $e;
	}
	/**
	 * @return Throwable|null
	 */
	public function getException(): ?Throwable{
		return $this->appends['exception'] ?? null;
	}
	/**
	 * @return QMButton|null
	 */
	public function getConnectorUserProfileButton(): ?QMButton{
		$c = $this->getQMConnector();
		if(!method_exists($c, 'getUserProfilePageButton')){
			return null;
		}
		/** @var \App\DataSources\HasUserProfilePage $c */
		if(!$c->getUserProfilePageUrl()){return null;}
		try {
			return $c->getUserProfilePageButton();
		} catch (ConnectorException $e) {
			$this->logError(__METHOD__.": ".$e->getMessage());
			return null;
		}
	}
	protected function unsetErrorMessages(): void{
		foreach(Connection::ERROR_FIELDS as $field){$this->setAttribute($field, null);}
	}
	/**
	 * @param string|null $reason
	 * @throws ConnectorDisabledException
	 * @throws NoGeoDataException
	 */
	public function incrementalImport(string $reason = null){
		$c = $this->getQMConnector();
		$c->setFromDate($c->getIncrementalFromTime());
		$this->import($reason);
	}
	/**
	 * @param string|null $reason
	 * @throws ConnectorDisabledException
	 * @throws NoGeoDataException
	 */
	public function fullImport(string $reason = null){
		$c = $this->getQMConnector();
		$c->setFromDate($c->getAbsoluteFromAt());
		$this->import($reason);
	}
	/**
	 * @return int
	 */
	public function calculateNumberOfMeasurements(): int{
		return $this->calculateAttribute(ConnectionNumberOfMeasurementsProperty::NAME);
	}
	public function getMeasurementHistoryButton(): ?HistoryAllStateButton{
		if($this->getNumberOfMeasurements() || $this->total_measurements_in_last_update){
			$params = [
				Measurement::FIELD_CONNECTOR_ID => $this->getConnectorId(),
				Measurement::FIELD_SOURCE_NAME => $this->getConnectorName(),
			];
			$params[Measurement::FIELD_CONNECTION_ID] = $this->getId();
			return new HistoryAllStateButton($params);
		}
		return null;
	}
	/**
	 * @return ConnectorImport
	 */
	public function getConnectorImport(): ConnectorImport{
		return $this->getQMConnector()->getConnectorImport();
	}
	/**
	 * @return string
	 */
	public function getTitleAttribute(): string{
		if(!$this->hasId()){
			return static::getClassNameTitle();
		}
		return $this->getQMConnector()->getTitleAttribute();
	}
	public function validatePostImport(): void{
		ConsoleLog::info(__METHOD__." for $this");
		$connector = $this->getQMConnector();
		$variables = $connector->getQMUserVariables();
		foreach($variables as $v){
			ConsoleLog::debug(__METHOD__." makeSureLatestTimeOnVariableNotGreaterThanLatestForConnector for $v...");
			try {
				$this->makeSureLatestTimeOnVariableNotGreaterThanLatestForConnector($v);
			} catch (\Throwable $e) {
				$this->makeSureLatestTimeOnVariableNotGreaterThanLatestForConnector($v);
			}
			if($v->getNumberOfMeasurements()){
				$common = $v->getCommonVariable();
				ConsoleLog::debug(__METHOD__." getDataSourcesCount for $v...");
				$sources = $common->getDataSourcesCount();
				ConsoleLog::debug(__METHOD__." calculateDataSourcesCount for $v...");
				$v->calculateDataSourcesCount();
				if(!isset($sources[$connector->displayName])){
					QMLog::exceptionIfTesting("$v->name COMMON variable does not have $connector->displayName in DataSourcesCount: " .
						QMLog::print_r($sources, true));
				}
			}
		}
	}
	/**
	 * @param QMUserVariable $v
	 */
	protected function makeSureLatestTimeOnVariableNotGreaterThanLatestForConnector(QMUserVariable $v): void{
		$latestForConnector = $this->getLatestMeasurementAtAttribute();
		$latestTaggedMeasurementAtProperty = $v->getLatestTaggedMeasurementAt();
		if($latestTaggedMeasurementAtProperty > $latestForConnector){
			$lastRawMeasurementForVariable = $v->getLastRawNonTaggedMeasurement();
			$latestForVariableFromLastMeasurement = $lastRawMeasurementForVariable->getStartAt();
			/** @var QMMeasurement $lastRawMeasurementForVariable */
			$thisConnector = $this->getQMConnector();
			$measurementConnector = $lastRawMeasurementForVariable->getDataSource();
			if($measurementConnector && $measurementConnector->getNameAttribute() === $thisConnector->getNameAttribute()){
				$message =
					"Latest for $v $latestForVariableFromLastMeasurement is greater than latest for $this $latestForConnector";
				if(AppMode::isAnyKindOfUnitTest() && !AppMode::isStagingUnitTesting()){
					le($message);
				} else{
					$this->logError($message);
					$this->setLatestMeasurementAtAttribute($lastRawMeasurementForVariable->getStartAt());
				}
			}
		}
	}
	/**
	 * @return QMMeasurement|null
	 */
	public function getLastMeasurement(): ?QMMeasurement{
		$row = QMMeasurement::qb()->where(Measurement::FIELD_CONNECTOR_ID, $this->getConnectorId())
			->orderBy(Measurement::FIELD_START_TIME, 'desc')->first();
		if(!$row){
			return null;
		}
		return QMMeasurement::instantiateIfNecessary($row);
	}
	/**
	 * @return array
	 */
	public function getOrSetNewMeasurementsByVariableName(): array{
		return $this->getQMConnector()->getOrSetNewMeasurementsByVariableName();
	}
	/**
	 * @return int|null
	 */
	public function getLastImportTime(): ?int{
		$at = $this->getImportEndedAt();
		if(!$at){
			return null;
		}
		return strtotime($at);
	}
	/**
	 * @return string|null
	 */
	public function getNewMeasurementsMessage(): ?string{
		$n = $this->total_measurements_in_last_update;
		$importEndedAt = $this->getImportEndedAt();
		if($importEndedAt){
			return "Imported $n new measurements in the most recent import ".
			       TimeHelper::timeSinceHumanString($importEndedAt).".\n";
		}
		if($n){
			QMLog::exceptionIfTesting("No import ended at on connection", [], false,
			             "No import ended at even though we have $n measurements for $this");
		}
		return "Imported $n new measurements in the most recent import.\n";
	}
	/**
	 * @return string|null
	 */
	public function getTotalMeasurementsMessage(): ?string{
		$n = $this->getNumberOfMeasurements();
		if($lasImport = $this->total_measurements_in_last_update){
			if($lasImport > $n){
				$this->exceptionIfTesting("Why do we have $lasImport measurements in last import but total Measurements are $n".
					Measurement::getAstralIndexPath());
			}
		}
		$t = $this->getEarliestMeasurementAt();
		$early = TimeHelper::YYYYmmddd($t);
		return $this->getNumberOfMeasurements() .
			" total measurements from $early" .
			" to {$this->getOrCalculateLatestMeasurementDate()}.";
	}
	/**
	 * @return string
	 */
	public function getErrorMessages(): string{
		$message = '';
		$connectStatus = $this->connect_status;
		if($connectStatus === ConnectionConnectStatusProperty::CONNECT_STATUS_ERROR ||
			$connectStatus === ConnectionConnectStatusProperty::CONNECT_STATUS_EXPIRED){
			if($this->connect_error){
				$message .= $this->connect_error . "\n";
			}
		}
		$updateError = $this->getUpdateErrorString();
		if($updateError && !SecretHelper::findSecretNamePattern($updateError) &&
			stripos($message, $updateError) === false){
			$message .= $updateError . "\n";
		}
		if(!empty($message)){
			$message .= QMConnector::RECONNECT_MESSAGE . "\n";
		}
		return $message;
	}
	/**
	 * @return Builder|Connection|\Illuminate\Database\Eloquent\Builder
     */
	public static function whereStale(){
		$qb = static::where(static::TABLE . '.' . static::FIELD_IMPORT_ENDED_AT, "<",
			db_date(time() - self::SECONDS_BETWEEN_IMPORTS));
		$qb = $qb->whereRaw(static::TABLE . '.' . static::FIELD_IMPORT_STARTED_AT . " < " . static::TABLE . '.' .
			static::FIELD_IMPORT_ENDED_AT)
			->where('connect_status', ConnectionConnectStatusProperty::CONNECT_STATUS_CONNECTED)
			->orderBy(Connection::FIELD_IMPORT_ENDED_AT, 'ASC');
		QMUser::excludeTestAndDeletedUsers($qb, Connection::TABLE);
		Connection::excludeNonApiUpdateAndDisabledConnectors($qb);
		return $qb;
	}
	/**
	 * @return \Illuminate\Database\Eloquent\Builder|QMQB|Connection
	 * @noinspection PhpDocSignatureInspection
	 */
	public static function whereWaiting(): \Illuminate\Database\Eloquent\Builder|QMQB{
		$qb = static::where(static::TABLE . '.' . static::FIELD_UPDATE_STATUS,
			ConnectionUpdateStatusProperty::IMPORT_STATUS_WAITING)
			->where(Connection::fieldString(static::FIELD_CONNECT_STATUS),
				ConnectionConnectStatusProperty::CONNECT_STATUS_CONNECTED)
			// Use IMPORT_STARTED_AT to orderBy so we do nulls first
			->orderBy(self::TABLE . '.' . Connection::FIELD_IMPORT_STARTED_AT, 'ASC');
		Connection::excludeNonApiUpdateAndDisabledConnectors($qb);
		QMUser::excludeTestAndDeletedUsers($qb, Connection::TABLE);
        $count = $qb->count();
        QMLog::info("$count waiting connections");
		return $qb;
	}
	/**
	 * @param \Illuminate\Database\Query\Builder|QMQB|null $qb
	 * @return QMQB|Builder
	 */
	public static function whereStuck($qb = null){
		if(!$qb){
			$qb = self::query();
		}
		$qb->where(static::TABLE . '.' . static::FIELD_IMPORT_STARTED_AT, "<",
			db_date(time() - self::MINIMUM_SECONDS_BETWEEN_IMPORT_ATTEMPTS));
		//$qb->whereRaw(static::TABLE.'.'.static::FIELD_IMPORT_STARTED_AT.' < '.static::TABLE.'.'.static::FIELD_IMPORT_ENDED_AT);
		$qb->where(static::TABLE . '.' . self::FIELD_CONNECT_STATUS,
			ConnectionConnectStatusProperty::CONNECT_STATUS_CONNECTED)->where(static::TABLE . '.' .
			Connection::FIELD_UPDATE_STATUS, ConnectionUpdateStatusProperty::IMPORT_STATUS_IMPORTING);
		self::orderByImportStartedAt($qb);
		self::excludeNonApiUpdateAndDisabledConnectors($qb);
		self::excludeTestAndDeletedUsers($qb);
		return $qb;
	}
	/**
	 * @param Throwable $e
	 */
	private function handleCredentialsNotFound(Throwable $e): void{
		$this->saveImportResult($e, Connection::TEMPORARY_IMPORT_ISSUE_USER_ERROR_MESSAGE);
		le("Connections with missing tokens would be a result of flawed code so should this exception should not be handled automatically. " .
			"Updates should stop and the cause for disappearance should be identified and connections disconnected with Jobs/Cleanup/ConnectorsCleanUpJobTest.php. " .
			$e->getMessage());
	}
	/**
	 * @param $connectorNameOrId
	 * @return Connection[]
	 */
	public static function getAllForConnector($connectorNameOrId): array{
		$connector = QMConnector::getDataSourceByNameOrIdOrSynonym($connectorNameOrId);
		$rows = self::readonly()->where(Connection::FIELD_CONNECTOR_ID, $connector->getId())->getDBModels();
		return $rows;
	}
	/**
	 * @param $connectorNameOrId
	 * @return Connection[]
	 */
	public static function getAllConnectedForConnector($connectorNameOrId): array{
		$connector = QMConnector::getDataSourceByNameOrIdOrSynonym($connectorNameOrId);
		$rows = self::readonly()->whereNull(self::FIELD_DELETED_AT)
			->where(self::FIELD_CONNECT_STATUS, ConnectionConnectStatusProperty::CONNECT_STATUS_CONNECTED)
			->where(Connection::FIELD_CONNECTOR_ID, $connector->getId())->getDBModels();
		return $rows;
	}
	/**
	 * @param QMQB|Builder|\Illuminate\Database\Eloquent\Builder $qb
	 */
	public static function excludeNonApiUpdateAndDisabledConnectors($qb){
		$connectors = QMConnector::getConnectors();
		$ids = Connector::whereEnabled(0)->pluck(Connector::FIELD_ID)->all();
		foreach($connectors as $connector){
			$disabled = $connector->isDisabled();
			if(!$connector->isImportViaApi() || $disabled){
				$ids[] = $connector->id;
			}
		}
        $ids = array_unique($ids);
		$qb->whereNotIn(self::TABLE . '.' . self::FIELD_CONNECTOR_ID, $ids);
	}
	/**
	 * @return Connection
	 */
	public static function getOldestWaitingStaleOrStuckConnection(): ?Connection{
		/** @var Connection $c */
		$c = Connection::whereWaiting()->first();
		if(!$c){
			$c = Connection::whereStale()->first();
		}
		if(!$c){
			$c = Connection::whereStuck()->first();
		}
		return $c;
	}
	/**
	 * @param QMQB|Builder $qb
	 */
	private static function excludeTestAndDeletedUsers($qb){
		QMUser::excludeTestAndDeletedUsers($qb, static::TABLE);
	}
	/**
	 * @return DBModel|stdClass|null
	 */
	public function getCredentialsRow(): DBModel|stdClass|null{
		return CredentialStorage::readonly()
            ->where(CredentialStorage::FIELD_connector_id, $this->getConnectorId())
			->where(CredentialStorage::FIELD_USER_ID, $this->getUserId())
			->first();
	}
	/**
	 * @param string|null $userMessage
	 */
	public function connect(string $userMessage = null, array $meta = []){
		$this->update_requested_at = now_at();
		$this->user_message = $userMessage;
		if(isset($meta['id'])){$this->connector_user_id = $meta['id'];}
		if(isset($meta['email'])){$this->connector_user_email = $meta['email'];}
		$this->connect_status = ConnectionConnectStatusProperty::CONNECT_STATUS_CONNECTED;
		$this->unsetErrorMessages();
		$this->update_status = ConnectionUpdateStatusProperty::IMPORT_STATUS_WAITING;
		if(AppMode::isApiRequest()){
			if($clientId = BaseClientIdProperty::fromRequest(false)){$this->client_id = $clientId;}
		}
		if($meta){$this->meta = $meta;}
		$this->save();
	}
	/**
	 * @param array $arr
	 * @param string|null $reason
	 * @return int
	 */
	public function updateDbRow(array $arr, string $reason = null): int{
		if(AppMode::isApiRequest()){
			if($clientId = BaseClientIdProperty::fromRequest(false)){
				$arr[self::FIELD_CLIENT_ID] = $clientId;
			}
		}
		return parent::updateDbRow($arr);
	}
	/**
	 * @param string $internalMessage
	 * @param string|null $userMessage
	 */
	public function disconnect(string $internalMessage, string $userMessage = null): void{
		if($internalMessage === QMConnector::USER_DISCONNECT_REQUEST || 
		   str_contains($internalMessage, "test")){
			$this->logInfo("Disconnecting and deleting credentials for {$this->getUser()} because $internalMessage");
		} else{
			$this->logError("Disconnecting and soft-deleting credentials for {$this->getUser()} because
			 $internalMessage");
		}
		$c = $this->connector;
		$this->updateDbRow([
			self::FIELD_CONNECT_STATUS => ConnectionConnectStatusProperty::CONNECT_STATUS_DISCONNECTED,
			self::FIELD_UPDATE_STATUS => ConnectionUpdateStatusProperty::IMPORT_STATUS_DISCONNECTED,
			self::FIELD_UPDATE_ERROR => $internalMessage,
			self::FIELD_INTERNAL_ERROR_MESSAGE => $internalMessage,
			self::FIELD_USER_MESSAGE => $userMessage,
		]);
        $this->getCredentialStorageFromMemory()->softDeleteCredentials($internalMessage);
		$this->getUser()->setConnections(null);
		//$c->detectRecursion();
	}
    /**
     * @return CredentialStorage
     */
    public function getCredentialStorageFromMemory(): CredentialStorage{
        $cs = CredentialStorage::findInMemoryWhere(['userId' => $this->getUserId(),
            'connectorId' => $this->getConnectorId()]);
        if(!$cs){
            $cs = new CredentialStorage($this);
        }
        return $cs;
    }
	/**
	 * Check whether the given $userId has $connectorId connected.
	 * @return bool
	 * @throws InvalidArgumentException
	 */
	public function isConnected(): bool{
		return $this->connect_status === ConnectionConnectStatusProperty::CONNECT_STATUS_CONNECTED;
	}
	/**
	 * @param string $userMessage
	 * @param string $internalMessage
	 * @return null
	 */
	public function setImportErrorMessage(string $userMessage, string $internalMessage): ?int{
		$this->logError("User Error:
            $userMessage
            Internal Error:
                $internalMessage");
		return $this->updateDbRow([
			self::FIELD_UPDATE_STATUS => ConnectionUpdateStatusProperty::IMPORT_STATUS_ERROR,
			self::FIELD_UPDATE_ERROR => $userMessage,
			self::FIELD_INTERNAL_ERROR_MESSAGE => $internalMessage,
			self::FIELD_USER_ERROR_MESSAGE => $userMessage,
		]);
	}
	/**
	 * @param string $userMessage
	 * @param string $internalMessage
	 * @return null
	 */
	public function setConnectErrorMessage(string $userMessage, string $internalMessage): ?int{
		$this->logError("User Error:
            $userMessage
            Internal Error:
                $internalMessage");
		return $this->updateDbRow([
			self::FIELD_CONNECT_STATUS => ConnectionConnectStatusProperty::CONNECT_STATUS_ERROR,
			self::FIELD_CONNECT_ERROR => $userMessage,
			self::FIELD_INTERNAL_ERROR_MESSAGE => $internalMessage,
			self::FIELD_USER_ERROR_MESSAGE => $userMessage,
		]);
	}
	/**
	 * @param string $errorMessage
	 * @param array $metaData
	 */
	public function logErrorIfNotTestUser(string $errorMessage, array $metaData = []){
		if(!$this->getUser()->isTestUser()){
			$this->logError($errorMessage, $metaData);
		}
	}
	/**
	 * @param string $reason_for_import
	 */
	public function setReasonForImport(string $reason_for_import): void{
		if($this->reason_for_import && stripos($this->reason_for_import, $reason_for_import) !== false){
			return;
		}
		$this->reason_for_import = $reason_for_import;
	}
	public function logLinkToHistory(){
		$this->logInfoWithoutContext($this->getLinkToHistoryPage());
	}
	/**
	 * @return string
	 */
	public function getLinkToHistoryPage(): string{
		return IonicHelper::getHistoryUrl([
			self::FIELD_USER_ID => $this->getUserId(),
			self::FIELD_CONNECTOR_ID => $this->getConnectorId(),
		]);
	}
	public function logMeasurementsTable(){
		$this->getMeasurementsButton()->logLink();
	}
	public function getMeasurementsUrl(): string{
		return $this->getMeasurementsButton()->getUrl();
	}
	public function getMeasurementsButton(): ConnectionMeasurementsButton{
		return new ConnectionMeasurementsButton($this);
	}
	/**
	 * @return string|null
	 */
	public function getEarliestMeasurementAt(): ?string{
		if($this->relationLoaded('measurements')){
			$measurements = $this->getMeasurements();
			return $measurements->min(Measurement::FIELD_START_AT);
		} else {
			return $this->measurements()->min(Measurement::FIELD_START_AT);
		}
	}
	/**
	 * @param int $userId
	 * @param int $connectorId
	 * @return Connection
	 */
	public static function getConnectionById(int $userId, int $connectorId): ?Connection{
		$u = QMUser::find($userId);
		$connections = $u->getConnections();
		if(!$connections){
			return null;
		}
		return QMArr::firstWhere(self::FIELD_CONNECTOR_ID, $connectorId, $connections);
	}
	/**
	 * @return string
	 */
	public function getUpdateStatus(): string{
		return $this->update_status;
	}
	/**
	 * @return string
	 */
	public function getUpdateErrorString(): ?string{
		$string = $error = $this->update_error;
		if($string && !is_string($string)){
			$string = '';
			if(isset($error->error)){
				$string .= $error->error;
			}
			if(isset($error->error_description)){
				$string .= ': ' . $error->error_description;
			}
		}
		return $string;
	}
	/**
	 * @return string
	 */
	public function getConnectStatus(): string{
		return $this->connect_status;
	}
	/**
	 * @param int|string $at
	 * @return int
	 */
	public static function numberImportedSince($at): int{
		return self::readonly()->where(self::FIELD_IMPORT_ENDED_AT, '>', db_date($at))->count();
	}
	/**
	 * @return string
	 */
	public function __toString(){
		$str = "Connection ID: $this->id\n";
		if($this->connector_id){
			$connectorName = QMConnector::idToName($this->connector_id);
			if($connectorName){
				$str .= "Connector: $connectorName\n";
			} else {
				$str .= "Connector ID: $this->connector_id\n";
			}
		}
		if($this->user_id){
			$user = User::findInMemory($this->user_id);
			if($user){
				$str .= "User: " . User::findInMemory($this->user_id)->getTitleAttribute() . "\n";
			} else {
				$str .= "User ID: $this->user_id\n";
			}
		}
		return $str;
    }
	public function getImportRange(): string{ return $this->getQMConnector()->getImportRange(); }
    public function logImportUrl(): string{
        $url = $this->getImportUrl();
        $this->logInfo("Import in browser at:\n".$url);
        return $url;
    }
    /**
     * @param string|null $reason
     * @throws ConnectorDisabledException
     * @throws NoGeoDataException
     */
	public function import(string $reason = null): void{
		$connectorName = $this->getConnectorName();
		$user = $this->getUser();
		$titleAttribute = $user->getTitleAttribute();
		$importUrl = $this->getImportUrl();
		$processName = __METHOD__." for ".$connectorName." for ".$titleAttribute."\n".$importUrl;
		QMLog::logStartOfProcess($processName);
		sleep(1); // Avoid too fast duplicate import record
		$connector = $this->getQMConnector();
		if(!$connector->isImportViaApi()){
			le("Not importing because isUpdateViaApi is false");
		}
		if($reason){
			$this->setReasonForImport($reason);
		} else{
			$reason = $this->reason_for_import;
		}
        $fromAt = $connector->getFromAt();
        $connector->getImportStartTime();
        $connector->setFromDate($fromAt);
        $this->logImportUrl();
        $this->logPHPUnitTest();
        $message = "Importing from {$connector->getImportRange()} because $reason...";
		$this->updateDbRow([
			self::FIELD_IMPORT_STARTED_AT => now_at(),
			self::FIELD_REASON_FOR_IMPORT => QMStr::truncate($reason, 250),
			self::FIELD_UPDATE_STATUS => ConnectionUpdateStatusProperty::IMPORT_STATUS_IMPORTING,
			self::FIELD_INTERNAL_ERROR_MESSAGE => null,
			self::FIELD_CONNECT_ERROR => null,
			self::FIELD_USER_ERROR_MESSAGE => null,
			self::FIELD_UPDATE_ERROR => null,
			Connection::FIELD_NUMBER_OF_MEASUREMENTS => $this->calculateNumberOfMeasurements(),
			self::FIELD_USER_MESSAGE => $message,
		], $message);
		if(!TimeHelper::isPast($fromAt)){
			$updateError = "Not importing because fromAt is in the future: $fromAt";
			$this->logError($updateError);
			QMLog::logEndOfProcess($processName);
			$this->update_error = $this->internal_error_message = $updateError;
			$this->update_status = ConnectionUpdateStatusProperty::IMPORT_STATUS_UPDATED;
			$this->save();
			return;
		}
		$this->limitImportDurationToOneHour();
		try {
            $this->logInfo($message);
			$connector->importData();
			$this->saveImportResult(null, null);
		} catch (RecentImportException $e) {
			$this->saveImportResult($e, "Too soon to import again. ");
		} catch (\App\Exceptions\NoGeoDataException $e) {
            $this->disconnect(__METHOD__.": ".$e->getMessage());
			$this->saveImportResult($e, "No valid geolocation data. ");
		} catch (ConnectorException $e) {
			$this->saveImportResult($e, $e->getUserMessage());
		} catch (TemporaryImportException | ServerException $e) {
			$this->saveImportResult($e, self::TEMPORARY_IMPORT_ISSUE_USER_ERROR_MESSAGE);
		} catch (ClientException $e) {
			$this->internal_error_message = $e->getMessage();
			$this->save();
			/** @var \GuzzleHttp\Psr7\Response $r */
			$r = $e->getResponse();
			$code = $r->getStatusCode();
			$meta = [
				'body' => $r->getBody(),
				'reason' => $r->getReasonPhrase(),
			];
			if($code === 403 || $code === QMException::CODE_UNAUTHORIZED){
				$this->logError("Got $code response from $connector so disconnecting connection created " .
					$this->getTimeSinceCreatedAt(), $meta);
				$this->disconnect(self::TRY_RECONNECTING_USER_ERROR_MESSAGE);
			} elseif($code === 429){
				$this->logError($e->getMessage(), $meta);
				$this->saveImportResult($e, self::TEMPORARY_IMPORT_ISSUE_USER_ERROR_MESSAGE);
			} else{
				le($e, $meta);
			}
		} /** @noinspection PhpRedundantCatchClauseInspection */ catch (TokenNotFoundException | CredentialsNotFoundException $e) {
			$this->handleCredentialsNotFound($e);
		} catch (Throwable $e) {
			//$this->saveImportResult($e, self::TEMPORARY_IMPORT_ISSUE_USER_ERROR_MESSAGE);
			$this->logInfo($this->getDataLabShowUrl() . "\nPlease add catch clause to " . __METHOD__ .
				" for this exception:\n" . TestFailure::exceptionToString($e));
			/** @var LogicException $e */
			throw $e;
		}
		$user = $this->getUser();
		$userVariables = $connector->getQMUserVariables();
		foreach($userVariables as $userVariable){
			$userVariable->analyze(__FUNCTION__);
		}
		QMLog::logEndOfProcess($processName);
	}
	private function limitImportDurationToOneHour(){
		set_time_limit(60 * 60); //Don't let script run more than an hour
	}
	/**
	 * @param Throwable|null $e
	 * @param string|null $userErrorMessage
	 * @return bool
	 */
	private function saveImportResult(Throwable $e = null, string $userErrorMessage = null): bool{
		ConsoleLog::info(__METHOD__." for $this...");
		// Don't do this because it breaks the casting function if($e){$this->setException($e);}
		$internalErrorMessage = null;
		if($e){
			$class = QMStr::toShortClassName(get_class($e));
			$job = AppMode::getJobTaskOrTestName();
			$internalErrorMessage = "$job: $class : ".$e->getMessage();
		}
		$connector = $this->getQMConnector();
		$number = count($connector->getNewMeasurements());
		if(!$number){
			$connector->getNewMeasurements();
			$this->logError("No new measurements for this!");
		}
		$latestAt = $this->getLatestMeasurementAtAttribute();
		try {
			$this->validateAndSetAttribute(Connection::FIELD_LATEST_MEASUREMENT_AT, $latestAt);
		} catch (InvalidAttributeException $e) {
			le($e);
		}
		if($number){ // Don't waste time on this if there are no new measurements
			$this->validatePostImport();
		}
		$this->logInfo("Saved $number new measurements for " . $this->getConnectorName());
		GoogleAnalyticsEvent::logEventToGoogleAnalytics("ConnectorImport", "Imported " . $this->getConnectorName(),
			$number, $this->getUserId(), $this->getClientId() ?? BaseClientIdProperty::fromMemory());
		$i = $this->getConnectorImport();
		$this->import_ended_at = now_at();
		$i->number_of_measurements = $this->total_measurements_in_last_update = $number;
		$this->countMeasurements();
		$this->connect_error = null;
        if($userErrorMessage && strlen($userErrorMessage) > (new ConnectionUserErrorMessageProperty)->maxLength){
            le("user error message is too long: " . $this->getImportUrl()."\n IT IS: ".$userErrorMessage);
        }
		$i->user_error_message = $this->user_error_message = $userErrorMessage;
		$i->internal_error_message = $this->internal_error_message = $this->update_error = $internalErrorMessage;
		$i->import_ended_at = $this->import_ended_at;
		$fromAt = $connector->getFromAt();
		$endAt = $connector->getEndAt();
		if($internalErrorMessage){
			$this->update_status = ConnectionUpdateStatusProperty::IMPORT_STATUS_ERROR;
		} else{
			$this->update_status = ConnectionUpdateStatusProperty::IMPORT_STATUS_UPDATED;
			$this->import_ended_at = now_at();
			//$i->importedDataEndAt =
			$this->imported_data_end_at = $endAt;
			//$i->importedDataFromAt =
			$this->imported_data_from_at = $fromAt;
			$this->user_message = $this->getTotalMeasurementsMessage();  // This is only available right after import or
			// we'll have to create fields for earliest and latest measurements
			$this->latest_measurement_at = $this->calculateLatestMeasurementAt();
			$this->calculateNumberOfMeasurements();
		}
		if($latestAt){
			$this->setLatestMeasurementAtAttribute($latestAt);
		}
		$new = Arr::flatten($this->getOrSetNewMeasurementsByVariableName(), 1);
		if($new){
			$i->earliest_measurement_at = collect($new)->min('startAt');
			$i->latest_measurement_at = collect($new)->max('startAt');
			if(!$i->earliest_measurement_at){
				le("No earliest_measurement_at!");
			}
		}
		try {$i->save();} catch (ModelValidationException $e) {le($e);}
		$connector->saveConnectorRequests();
		if(!$this->latest_measurement_at){
			$this->latest_measurement_at = null;
		} // Otherwise validation fails
		return $this->save();
	}
    public function getImportUrl(): string{
        return ApiConnectorController::getURL(['id' => $this->getId()]);
    }
	public function validate(): void{
		$userErrorMessage = $this->user_error_message;
		if($userErrorMessage && str_contains($userErrorMessage, "Exception")){
			le("User error message should not contain exception but is $userErrorMessage");
		}
		$internalErrorMessage = $this->internal_error_message;
		if($internalErrorMessage && str_contains($internalErrorMessage, "TokenNotFoundExc") &&
		   str_contains($internalErrorMessage, "temporary")){
			le("User error message should not contain temporary when internal message is " .$internalErrorMessage);
		}
	}
	/**
	 * @param array $options
	 * @return bool
	 */
	public function save(array $options = []): bool{
        try {
	        $QMConnector = $this->getQMConnector();
	        $QMConnector->addConnectionInfo($this);
        } catch (\Throwable $e) {
            $this->logError(__METHOD__.": ".$e->getMessage(), ['exception' => $e]);
        }
        if(!isset($this->attributes[self::FIELD_UPDATE_STATUS])){
            $this->update_status = ConnectionUpdateStatusProperty::IMPORT_STATUS_DISCONNECTED;
        }
		try {
			return parent::save();
		} catch (ModelValidationException $e) {
			le($e);
		}
	}
	/**
	 * @return QMConnector
	 */
	public function getQMConnector(): QMConnector{
		$user = $this->getUser();
		$c = $user->getQMConnector($this->connector_id);
		return $c;
	}
	/**
	 * @return ConnectorResponse
	 */
	public function requestImport(): ConnectorResponse{
		//ImportFromAPIJob::dispatch($this);
		$response = new ConnectorResponse($this->getQMConnector(), $this->getConnectorName() . '/update');
		if($this->connect_status === ConnectionConnectStatusProperty::CONNECT_STATUS_CONNECTED &&
			$this->update_status === ConnectionUpdateStatusProperty::IMPORT_STATUS_WAITING){
			if(!$this->getUser()->isTestUser()){
				$this->logError("Status already " . ConnectionUpdateStatusProperty::IMPORT_STATUS_WAITING);
			}
			return $response;
		}
		$this->logInfo("Requesting import...");
		$status = ConnectionUpdateStatusProperty::IMPORT_STATUS_WAITING;
		if(!$this->getQMConnector()->isImportViaApi()){
			$status = ConnectionUpdateStatusProperty::IMPORT_STATUS_UPDATED;
		}
		$this->updateDbRow([
			self::FIELD_UPDATE_REQUESTED_AT => now_at(),
			self::FIELD_UPDATE_STATUS => $status,
			self::FIELD_CONNECT_STATUS => ConnectionConnectStatusProperty::CONNECT_STATUS_CONNECTED,
			self::FIELD_UPDATE_ERROR => null,
		]);
		return $response;
	}
	public function getImportEndedAt(): ?string{
		return $this->import_ended_at;
	}
	public function getImportStartedAt(): ?string{
		return $this->attributes[Connection::FIELD_IMPORT_STARTED_AT] ?? null;
	}
	public function getIsPublic(): ?bool{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Connection::FIELD_IS_PUBLIC] ?? null;
		} else{
			return $this->isPublic;
		}
	}
	public function getNumberOfMeasurements(): int{
		if($this->number_of_measurements === null){
			$this->countMeasurements();
		}
		return $this->number_of_measurements;
	}
	/**
	 * @return RelationshipButton[]
	 */
	public function getInterestingRelationshipButtons(): array{
		return [
			new ConnectionUserButton($this),
			new ConnectionConnectorButton($this),
		];
	}
	public function getSortingScore(): float{
		return $this->getNumberOfMeasurements();
	}
	/**
	 * @return string|null
	 */
	public function getOrCalculateLatestMeasurementDate(): ?string{
		$t = $this->getOrCalculateLatestMeasurementAt();
		if(!$t){
			return null;
		}
		return TimeHelper::YYYYmmddd($t);
	}
	/**
	 * @return string|null
	 */
	public function getOrCalculateLatestMeasurementAt(): ?string{
		$at = $this->latest_measurement_at;
		if($at !== null){
			return $at;
		}
		if($this->getNumberOfMeasurements() === 0){
			return null;
		}
		return $this->calculateLatestMeasurementAt();
	}
	/**
	 * @return string|null
	 */
	public function calculateLatestMeasurementAt(): ?string{
		$at = ConnectionLatestMeasurementAtProperty::calculate($this);
		if(!$at){
			return null;
		}
		return $this->setLatestMeasurementAtAttribute($at);
	}
	/**
	 * @return string|null
	 */
	public function getLatestMeasurementAtAttribute(): ?string{
		return $this->attributes[self::FIELD_LATEST_MEASUREMENT_AT] ?? null;
	}
	/**
	 * @param int|string|null|CarbonInterface $at
	 * @return string|null
	 */
	public function setLatestMeasurementAtAttribute(int|CarbonInterface|string|null $at): ?string{
		return $this->attributes[self::FIELD_LATEST_MEASUREMENT_AT] = ($at) ? db_date($at) : $at;
	}
	public static function syncCredentialsToConnectionsTable(){
		$connections = Connection::all();
		foreach($connections as $connection){
			$credentials = $connection->getQMConnector()->getCredentialsArray();
			$connection->credentials = $credentials;
			$connection->save();
		}
	}
	public function getCredentialsAttribute(): array|object|null{
		$val = $this->attributes[self::FIELD_CREDENTIALS] ?? null;
		if(is_string($val)){
			return OAuthAuthenticatorCast::decode($val);
		}
		return $val;
	}
    public function addMeta(string $key, mixed $value){
		$key = QMStr::snakize($key);
		$meta = $this->attributes[self::FIELD_META] ?? [];
		if(is_string($meta)){
			$meta = json_decode($meta, true);
		}
		$meta[$key] = $value;
		$this->attributes[self::FIELD_META] = $meta;
    }
	public function getMessage(): string {
		$message = '';
		if($m = $this->user_message){
			if(!str_contains($m, "Try reconnecting")){
				$message .= "$m\n";
			}
		}
		if($this->total_measurements_in_last_update !== null){
			$message .= $this->getNewMeasurementsMessage();
		}
		return $message;
	}
	/**
	 * @param string $message
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function setUserErrorMessageAttribute(string $message = null){
		$this->attributes[self::FIELD_USER_MESSAGE] = $message;
	}
	/**
	 * @return string|null
	 * @noinspection PhpUnused
	 */
	public function getUserErrorMessageAttribute(): ?string{
		$colName = self::FIELD_USER_ERROR_MESSAGE;
		$val = $this->attributes[$colName] ?? null;
		if($val){$val = $this->deleteAttributeIfInvalid($colName, $val);}
		return $val;
	}
	/**
	 * @return string|null
	 * @noinspection PhpUnused
	 */
	public function getUserMessageAttribute(): ?string{
		$colName = self::FIELD_USER_MESSAGE;
		$val = $this->attributes[$colName] ?? null;
		//if($val){$val = $this->deleteAttributeIfInvalid($colName, $val);}
		return $val;
	}
	/**
	 * @param string $colName
	 * @param mixed $val
	 * @return mixed|null
	 */
	private function deleteAttributeIfInvalid(string $colName, string $val): mixed{
		$prop = $this->getPropertyModel($colName);
		if(!$prop->isValid()){
			QMLog::error("Deleting invalid $colName: $val");
			$this->attributes[$colName] = null;
			$this->save();
			$val = null;
		}
		return $val;
	}

	public function getMostRecentMeasurements(): \Illuminate\Support\Collection{
		$qb = $this->measurements();
		$qb->orderBy(Measurement::FIELD_START_AT, 'desc');
		$qb->limit(10);
		$measurements = $qb->get();
		return $measurements;
	}
}
