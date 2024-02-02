<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Logging\ConsoleLog;
use App\Traits\HasModel\HasConnector;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Buttons\QMButton;
use App\Buttons\States\MeasurementAddStateButton;
use App\DataSources\Connectors\FitbitConnector;
use App\DataSources\QMConnector;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NoChangesException;
use App\Logging\QMLog;
use App\Models\Base\BaseMeasurement;
use App\Astral\UnitBaseAstralResource;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Measurement\MeasurementConnectionIdProperty;
use App\Properties\Measurement\MeasurementConnectorIdProperty;
use App\Properties\Measurement\MeasurementConnectorImportIdProperty;
use App\Properties\Measurement\MeasurementDurationProperty;
use App\Properties\Measurement\MeasurementIdProperty;
use App\Properties\Measurement\MeasurementLatitudeProperty;
use App\Properties\Measurement\MeasurementLocationProperty;
use App\Properties\Measurement\MeasurementLongitudeProperty;
use App\Properties\Measurement\MeasurementNoteProperty;
use App\Properties\Measurement\MeasurementOriginalUnitIdProperty;
use App\Properties\Measurement\MeasurementSourceNameProperty;
use App\Properties\Measurement\MeasurementStartTimeProperty;
use App\Properties\Measurement\MeasurementUserIdProperty;
use App\Properties\Measurement\MeasurementUserVariableIdProperty;
use App\Properties\Measurement\MeasurementVariableIdProperty;
use App\Properties\User\UserIdProperty;
use App\Properties\UserVariable\UserVariableVariableIdProperty;
use App\Properties\Variable\VariableIdProperty;
use App\Properties\Variable\VariableNameProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\DBModel;
use App\Slim\Model\Measurement\AdditionalMetaData;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\Measurement\QMMeasurementExtended;
use App\Slim\Model\QMUnit;
use App\Slim\View\Request\QMRequest;
use App\Storage\DB\Writable;
use App\Traits\HasDBModel;
use App\Traits\HasErrors;
use App\Traits\HasModel\HasDataSource;
use App\Traits\HasModel\HasImporterConnection;
use App\Traits\ModelTraits\MeasurementTrait;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\UI\QMColor;
use App\Utils\AppMode;
use App\Utils\Stats;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\QMCommonVariable;
use App\Variables\QMVariableCategory;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Fields\Avatar;
use App\Fields\DateTime;
use App\Fields\ID;
use App\Fields\Number;
use App\Fields\Text;
use OpenApi\Annotations as OA;
use Titasgailius\SearchRelations\SearchesRelations;
/**
 * App\Models\Measurement
 * @OA\Schema (
 *      definition="Measurement",
 *      required={"variable_id", "source_name", "start_time", "value", "unit_id"},
 *      @OA\Property(
 *          property="id",
 *          description="id",
 *          type="integer"
 *      ),
 *      @OA\Property(
 *          property="user_id",
 *          description="ID of user that owns this measurement",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="client_id",
 *          description="client_id",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="connector_id",
 *          description="The id for the connector data source from which the measurement was obtained",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="variable_id",
 *          description="ID of the variable for which we are creating the measurement records",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="source_name",
 *          description="Application or device used to record the measurement values",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="start_time",
 *          description="Start Time for the measurement event. Use ISO 8601",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="value",
 *          description="The value of the measurement after conversion to the default unit for that variable",
 *          type="number",
 *          format="float"
 *      ),
 *      @OA\Property(
 *          property="unit_id",
 *          description="The default unit for the variable",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="original_value",
 *          description="Value of measurement as originally posted (before conversion to default unit)",
 *          type="number",
 *          format="float"
 *      ),
 *      @OA\Property(
 *          property="original_unit_id",
 *          description="Unit ID of measurement as originally submitted",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="duration",
 *          description="Duration of the event being measurement in seconds",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="note",
 *          description="An optional note the user may include with their measurement",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="latitude",
 *          description="Latitude at which the measurement was taken",
 *          type="number",
 *          format="float"
 *      ),
 *      @OA\Property(
 *          property="longitude",
 *          description="Longitude at which the measurement was taken",
 *          type="number",
 *          format="float"
 *      ),
 *      @OA\Property(
 *          property="location",
 *          description="location",
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
 *      ),
 *      @OA\Property(
 *          property="error",
 *          description="error",
 *          type="string"
 *      )
 * )
 * @property integer $id
 * @property integer $user_id ID of user that owns this measurement
 * @property string $client_id
 * @property integer $connector_id The id for the connector data source from which the measurement was obtained
 * @property integer $variable_id ID of the variable for which we are creating the measurement records
 * @property string $source_name Application or device used to record the measurement values
 * @property string $start_time Start Time for the measurement event. Use ISO 8601
 * @property float $value The value of the measurement after conversion to the default unit for that variable
 * @property integer $unit_id The default unit for the variable
 * @property float $original_value Value of measurement as originally posted (before conversion to default unit)
 * @property integer $original_unit_id Unit ID of measurement as originally submitted
 * @property integer $duration Duration of the event being measurement in seconds
 * @property string $note An optional note the user may include with their measurement
 * @property float $latitude latitude Latitude at which the measurement was taken
 * @property float $longitude longitude Longitude at which the measurement was taken
 * @property string $location location
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $error An error message if there is a problem with the measurement
 * @method static \Illuminate\Database\Query\Builder|Measurement whereId($value)
 * @method static \Illuminate\Database\Query\Builder|Measurement whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|Measurement whereClientId($value)
 * @method static \Illuminate\Database\Query\Builder|Measurement whereConnectorId($value)
 * @method static \Illuminate\Database\Query\Builder|Measurement whereVariableId($value)
 * @method static \Illuminate\Database\Query\Builder|Measurement whereSourceId($value)
 * @method static \Illuminate\Database\Query\Builder|Measurement whereStartTime($value)
 * @method static \Illuminate\Database\Query\Builder|Measurement whereValue($value)
 * @method static \Illuminate\Database\Query\Builder|Measurement whereUnitId($value)
 * @method static \Illuminate\Database\Query\Builder|Measurement whereOriginalValue($value)
 * @method static \Illuminate\Database\Query\Builder|Measurement whereOriginalUnitId($value)
 * @method static \Illuminate\Database\Query\Builder|Measurement whereDuration($value)
 * @method static \Illuminate\Database\Query\Builder|Measurement whereNote($value)
 * @method static \Illuminate\Database\Query\Builder|Measurement whereLatitude($value)
 * @method static \Illuminate\Database\Query\Builder|Measurement whereLongitude($value)
 * @method static \Illuminate\Database\Query\Builder|Measurement whereLocation($value)
 * @method static \Illuminate\Database\Query\Builder|Measurement whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Measurement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Measurement whereError($value)
 * @property-read Connector $connector
 * @property-read QMUnit $measurement
 * @property-read QMUnit $originalUnit
 * @property-read QMUnit $unit
 * @property-read User $user
 * @property-read Variable $variable
 * @property int $variable_category_id Variable category ID
 * @property string|null $deleted_at
 * @method static Builder|Measurement newModelQuery()
 * @method static Builder|Measurement newQuery()
 * @method static Builder|Measurement query()
 * @method static Builder|Measurement whereDeletedAt($value)
 * @method static Builder|Measurement whereSourceName($value)
 * @method static Builder|Measurement whereVariableCategoryId($value)
 * @mixin Eloquent
 * @property int $user_variable_id
 * @method static Builder|Measurement whereUserVariableId($value)
 * @property \Illuminate\Support\Carbon|null $start_at
 * @method static Builder|Measurement whereStartAt($value)
 * @property-read OAClient|null $oa_client
 * @property-read UserVariable $user_variable
 * @property int|null $connection_id
 * @property int|null $connector_import_id
 * @method static Builder|Measurement whereConnectionId($value)
 * @method static Builder|Measurement whereConnectorImportId($value)
 * @property-read QMVariableCategory|null $variable_category
 * @property-read Connection|null $connection
 * @property-read ConnectorImport|null $connector_import
 * @property-read QMUnit $original_unit
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()

 * @property string|null $deletion_reason The reason the variable was deleted.
 * @method static Builder|Measurement whereDeletionReason($value)
 * @property string $original_start_at
 * @property mixed $raw
 * @property-read mixed $raw_variable
 * @method static Builder|Measurement whereOriginalStartAt($value)
 * @property-read OAClient $client
 */
class Measurement extends BaseMeasurement {
    use HasFactory;
    use HasConnector;
	use MeasurementTrait;
	use HasDataSource, HasImporterConnection, HasErrors, HasDBModel;
	use SearchesRelations;
	/**
	 * The single value that should be used to represent the resource when being displayed.
	 * @var string
	 */
	public static $title = Measurement::FIELD_ID;
	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [];
	/**
	 * The relationship columns that should be searched.
	 * @var array
	 */
	public static $searchRelations = [
		'variable' => ['name'],
	];
	public static $group = Measurement::CLASS_CATEGORY;
	/**
	 * Indicates if the resource should be globally searchable.
	 * @var bool
	 */
	public static $globallySearchable = false;
	/**
	 * The per-page options used the resource index.
	 * @var array
	 */
	public static $perPageOptions = [10, 25, 50, 100];
	/**
	 * The number of resources to show per page via relationships.
	 * @var int
	 */
	public static $perPageViaRelationship = 10;
	public static function getSlimClass(): string{ return QMMeasurement::class; }
	public const MAX_LIMIT = 1000;
	public const CLASS_DESCRIPTION = "Measurements are any value that can be recorded like daily steps, a mood rating, or apples eaten. ";
	public const COLOR = QMColor::HEX_FUCHSIA;
	public const FONT_AWESOME = FontAwesome::RULER_SOLID;
	public const DEFAULT_SEARCH_FIELD = 'variable.' . Variable::FIELD_NAME;
	public const DEFAULT_ORDERINGS = [self::FIELD_START_AT => self::ORDER_DIRECTION_DESC];
	public const DEFAULT_LIMIT = 20;
	public const DEFAULT_IMAGE = ImageUrls::FITNESS_MEASURING_TAPE;
	public static function getUniqueIndexColumns(): array{
		return [
			self::FIELD_USER_ID,
			self::FIELD_VARIABLE_ID,
			self::FIELD_START_TIME,
		];
	}
	const CLASS_CATEGORY = "Data";
	protected array $rules = [
		self::FIELD_USER_ID => 'required|numeric|min:1',
		self::FIELD_CLIENT_ID => 'nullable|max:80',
		self::FIELD_CONNECTOR_ID => 'nullable|integer|min:1|max:2147483647',
		self::FIELD_VARIABLE_ID => 'required|integer|min:1|max:2147483647',
		self::FIELD_START_TIME => 'required|integer|min:0|max:2147483647',
		self::FIELD_VALUE => 'required|numeric',
		self::FIELD_UNIT_ID => 'required|integer|min:1|max:65535',
		self::FIELD_ORIGINAL_VALUE => 'required|numeric',
		self::FIELD_ORIGINAL_UNIT_ID => 'required|integer|min:1|max:65535',
		self::FIELD_DURATION => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_LATITUDE => 'nullable|numeric',
		self::FIELD_LONGITUDE => 'nullable|numeric',
		self::FIELD_LOCATION => 'nullable|max:255',
		self::FIELD_ERROR => 'nullable|max:65535',
		self::FIELD_VARIABLE_CATEGORY_ID => 'required|integer|min:1|max:300',
		self::FIELD_SOURCE_NAME => 'nullable|max:80',
		self::FIELD_USER_VARIABLE_ID => 'required|integer|min:1|max:2147483647',
		self::FIELD_START_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_CONNECTION_ID => 'nullable|integer|min:1|max:2147483647',
		self::FIELD_CONNECTOR_IMPORT_ID => 'nullable|integer|min:1|max:2147483647',
	];
	protected $casts = [
		self::FIELD_CONNECTOR_ID => 'int',
		self::FIELD_DURATION => 'int',
		self::FIELD_ID => 'int',
		self::FIELD_LATITUDE => 'float',
		self::FIELD_LONGITUDE => 'float',
		self::FIELD_NOTE => 'array',
		self::FIELD_ORIGINAL_UNIT_ID => 'int',
		self::FIELD_ORIGINAL_VALUE => 'float',
		self::FIELD_START_TIME => 'int',
		self::FIELD_UNIT_ID => 'int',
		self::FIELD_USER_ID => 'int',
		self::FIELD_USER_VARIABLE_ID => 'int',
		self::FIELD_VALUE => 'float',
		self::FIELD_VARIABLE_CATEGORY_ID => 'int',
		self::FIELD_VARIABLE_ID => 'int',
	];
	protected array $openApiSchema = [
		self::FIELD_NOTE => ['type' => 'array', 'items' => ['type' => 'string']],
	];
	/**
	 * @param $combined
	 * @return bool
	 */
	public static function shouldBulkInsert($combined): bool{
		$shouldBulkInsert = count($combined) > 5;
		return $shouldBulkInsert;
	}
	/**
	 * @param array $target
	 * @param $source
	 * @return array
	 */
	public static function addConnectorInfo(array $target, $source): array{
		$target = MeasurementConnectorIdProperty::addToArrayIfPresent($target, $source);
		$target = MeasurementConnectionIdProperty::addToArrayIfPresent($target, $source);
		$target = MeasurementConnectorImportIdProperty::addToArrayIfPresent($target, $source);
		return $target;
	}
	/**
	 * @param array $target
	 * @param $source
	 * @return array
	 */
	public static function addLocationInfo(array $target, $source): array{
		$target = MeasurementLocationProperty::addToArrayIfPresent($target, $source);
		$target = MeasurementLatitudeProperty::addToArrayIfPresent($target, $source);
		$target = MeasurementLongitudeProperty::addToArrayIfPresent($target, $source);
		return $target;
	}
	/**
	 * @param array $target
	 * @param array $source
	 * @return array
	 */
	public static function populateOptionalMeasurementData(array $target, $source): array{
		$target = Measurement::addConnectorInfo($target, $source);
		$target = Measurement::addLocationInfo($target, $source);
		$target = MeasurementDurationProperty::addToArrayIfPresent($target, $source);
		$target = MeasurementNoteProperty::addToArrayIfPresent($target, $source);
		$target = MeasurementSourceNameProperty::addToArrayIfPresent($target, $source);
		return $target;
	}
	/**
	 * @param array $byVariable
	 * @return Measurement[]
	 */
	public static function flatten(array $byVariable): array{
		$flat = [];
		foreach($byVariable as $variableName => $measurements){
			foreach($measurements as $startAt => $m){
				$flat[$startAt] = $m;
			}
		}
		return $flat;
	}
	/**
	 * @param int $userId
	 * @param int|string $variableIdOrName
	 * @param int|string $startTimeAt
	 * @return bool
	 */
	public static function deleteByVariableUserStart(int $userId, $variableIdOrName, $startTimeAt): ?bool{
		$variableId = VariableIdProperty::pluckOrDefault($variableIdOrName);
		$startTime = MeasurementStartTimeProperty::pluck($startTimeAt);
		$result = static::whereUserAndVariable($userId,$variableId)
			->where(static::FIELD_START_TIME, $startTime)
			->forceDelete();
		if($result){return $result;}
		$rounded = MeasurementStartTimeProperty::pluckRounded([
			Measurement::FIELD_VARIABLE_ID => $variableId,
			Measurement::FIELD_START_TIME => $startTime,
		]);
		if($rounded === $startTime){return $result;}
		$result = static::whereUserAndVariable($userId,$variableId)
			->where(static::FIELD_START_TIME, $rounded)
			->forceDelete();
		if(!$result){
			$result = static::whereUserAndVariable($userId,$variableId)
				->where(static::FIELD_ORIGINAL_START_AT, db_date($startTimeAt))
				->forceDelete();
		}
		if(!$result){
			$result = static::whereUserAndVariable($userId,$variableId)
				->where(static::FIELD_ORIGINAL_START_AT, db_date($rounded))
				->forceDelete();
		}
		return $result;
	}
	/**
	 * @param int $userId
	 * @param int $variableId
	 * @return Measurement|Builder
	 */
	public static function whereUserAndVariable(int $userId, int $variableId){
		return static::where(self::FIELD_USER_ID, $userId)
            ->where(self::FIELD_VARIABLE_ID, $variableId);
	}
	/**
	 * @param array $values
	 * @param UserVariable $uv
	 * @return array
	 */
	public static function saveChanged(array $values, UserVariable $uv): array{
		$newMeasurements = [];
		$previous = $uv->getMeasurementsIndexedByStartAt();
		foreach($values as $one){
			$startAt = $one[Measurement::FIELD_START_AT];
			try {
				if($existing = $previous[$startAt] ?? null){
					$l = $existing;
				} else{
					$l = new Measurement();
				}
				$one = QMArr::removeNulls($one);
				$l->fill($one);
				$changes = $l->save();
				if($changes){
					$newMeasurements[] = $l;
				}
			} catch (NoChangesException $e) {
				QMRequest::addClientWarning(__METHOD__.": ".$e->getMessage());
				$duplicates[] = $one;
			}
		}
		if(!$uv->measurementsAreSet()){
			le('!$uv->measurementsAreSet()');
		}
		if($newMeasurements){
			$uv->updateFromMeasurements($newMeasurements);
		}
		return $newMeasurements;
	}
	/**
	 * @return BelongsTo
	 */
	public function source(): BelongsTo{
		return $this->belongsTo(Source::class);
	}
	/**
	 * @param int $limit
	 */
	public static function logVariableWithMostMeasurements(int $limit = 100){
		$ids = Writable::selectStatic("
            select m.variable_id as variable,
                   count(*) as measurements,
                   v.name as variable_name,
                   v.number_of_measurements as from_variables
            from measurements m
            join variables v on v.id = m.variable_id
            group by m.variable_id
            order by measurements desc
            limit $limit
        ;");
		QMLog::table($ids, 'Variables With Most Measurements');
	}
	public function getDataSourceAdminLink(): ?string{
		if($this->connector_id){
			return QMConnector::getByNameOrId($this->connector_id)->getDataLabDisplayNameLink();
		}
		if($this->source_name){
			try {
				return QMConnector::getByNameOrId($this->source_name)->getDataLabDisplayNameLink();
			} catch (\Throwable $e) {
				return $this->source_name;
			}
		}
		return null;
	}
	public function getImage(): string{
		if(!$this->hasId()){
			return static::DEFAULT_IMAGE;
		}
		$meta = $this->getAdditionalMetaData();
		$img = $meta->image ?? null;
		if($img){
			return $img;
		}
		if($cv = QMCommonVariable::findInMemory($this->variable_id)){
			return $cv->getImage();
		}
		return parent::getImage();
	}
	/**
	 * @return AdditionalMetaData
	 */
	public function getAdditionalMetaData(): AdditionalMetaData{
		return new AdditionalMetaData($this->note);
	}
	/**
	 * @param array $params
	 * @return QMButton
	 */
	public function getButton(array $params = []): QMButton{
		$meta = $this->getAdditionalMetaData();
        $b = new MeasurementAddStateButton($this);
		if(!$b->link){
			$b->setUrl($this->getUrl());
		}
		if(!$b->image){
			$b->setImage($this->getImage());
		}
		if(!$b->fontAwesome){
			$b->setFontAwesome($this->getFontAwesome());
		}
		return $b;
	}
	/**
	 * @param array $params
	 * @return QMButton
	 */
	public function getShowButton(array $params = []): QMButton{
		return $this->getButton($params);
	}
	public function getNameAttribute(): string{
		if(!$this->hasId()){
			return static::getClassNameTitle();
		}
		return $this->getValueUnitString() . " " . $this->getVariableName();
	}
	/**
	 * @param bool $useAbbreviatedName
	 * @param int $sigFigs
	 * @return string
	 */
	public function getValueUnitString(bool $useAbbreviatedName = false, int $sigFigs = 3): string{
		// Keep fully qualified name. Import doesn't work for some reason
		$v = Stats::roundByNumberOfSignificantDigits($this->value, $sigFigs);
		$u = $this->getQMUnit();
		return $u->getValueAndUnitString($v, $useAbbreviatedName);
	}
	public function getUserVariableButton(): QMButton{
		$title = $this->getVariable()->name . " User Variable";
		$url = UserVariable::generateShowUrl($this->user_variable_id);
		$b = $this->getVariable()->getButton();
		$b->setUrl($url);
		$b->setTextAndTitle($title);
		return $b;
	}
	public function getOriginalUnitLink(): string{
		return $this->getOriginalUnit()->getDataLabDisplayNameLink();
	}
	public function getOriginalUnit(): QMUnit{
		$u = QMUnit::find($this->original_unit_id);
		return $u;
	}
	/** @noinspection PhpUnused */
	public function getConnectorImportLink(): string{
		if(!$this->connector_import_id){
			return "N/A";
		}
		return ConnectorImport::generateDataLabShowUrl($this->connector_import_id);
	}
	public function getNoteMessage(): ?string{
		return $this->getAdditionalMetaData()->toHumanString();
	}
	public function getSubtitleAttribute(): string{
		if(!$this->hasId()){
			return static::CLASS_DESCRIPTION;
		}
		return $this->appends['subtitle'] = TimeHelper::timeSinceHumanString($this->start_at);
	}
	public function getStartTimeHtml(): string{
		$at = $this->start_at;
		$since = $this->getStartSince();
		return "<span title=\"$at\">$since</span>";
	}
	public function getLogMetaDataString(): string{
		$str = "";
		$unitId = $this->unit_id;
		if($unitId && isset(QMUnit::getUnitsIndexedById()[$unitId])){
			$str .= $this->getValueUnitString(true);
		}
		if($v = $this->getVariableFromMemory()){
			$str .= " $v for user " . $this->user_id;
		} else{
			$str .= " for variable " . $this->variable_id . " for user " . $this->user_id;
		}
		if($this->start_at){
			$str .= " at " . $this->start_at;
		} else{
			$str .= " at " . db_date($this->start_time);
		}
		return $str;
	}
	public function getMaterialStatCard(): string{
		$button = $this->getButton();
		return $button->getMaterialStatCard();
	}
	public function getFontAwesome(): string{
		if(isset($this->fontAwesome)){
			return $this->fontAwesome;
		}
		if(isset($this->variable_category_id)){
			return $this->getQMVariableCategory()->getFontAwesome();
		}
		return static::FONT_AWESOME;
	}
	public function getEditUrl(array $params = []): string{
		$params[self::FIELD_ID] = $this->getId();
		return MeasurementAddStateButton::make()->getUrl($params);
	}
	public static function newFake(int $userId = UserIdProperty::USER_ID_TEST_USER): BaseModel{
		$m = parent::newFake();
		$uv = OverallMoodCommonVariable::getUserVariableByUserId(UserIdProperty::USER_ID_DEMO);
		$m->populateByUserVariable($uv->l());
		$m->connector_id = FitbitConnector::ID;
		$m->setStartAtAttribute(time());
		$m->original_start_at = now_at();
		return $m;
	}

    /**
     * @param $data
     * @param bool $fallback
     * @return Measurement
     * @throws IncompatibleUnitException
     * @throws InvalidVariableValueException
     * @throws NoChangesException
     */
	public static function upsertOne($data, bool $fallback = false): BaseModel{
		$m = static::firstOrNewByData($data);
		$m->populate($data);
		if(AppMode::isApiRequest()){ // Need to alert the client.
			// TODO: Should probably figure out a way to check this for connectors as well
			$m->exceptionIfNoChanges($data);
		}
		$withNewUnitIfNecessary = UserVariable::upsertByRelated($data);
		$m->variable_id = $withNewUnitIfNecessary->variable_id;
		$m->user_variable_id = $withNewUnitIfNecessary->id;
		if($m->isDirty()){
			try {
				$m->save();
			} catch (ModelValidationException $e) {
				le($e);
			}
		}
		if(!$m->id){
			le('!$m->id');
		}
		// Don't do this here so we can do it in bulk $withNewUnitIfNecessary->updateFromMeasurements([$m]);
		return $m;
	}
	/**
	 * @param $data
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	public function populateForeignKeys($data){
		if(!$this->user_variable_id){
			$this->user_variable_id = MeasurementUserVariableIdProperty::pluck($data);
			if(!$this->user_variable_id){
				$uv = UserVariable::fromForeignData($data);
				$this->populateByUserVariable($uv);
			}
		}
		$this->original_unit_id = MeasurementOriginalUnitIdProperty::pluckOrDefault($data);
		parent::populateForeignKeys($data);
	}
	/**
	 * @param $data
	 * @return Measurement|null
	 */
	public static function findByUserVariableIfSet($data): ?Measurement{
		$rounded = MeasurementStartTimeProperty::pluckRounded($data);
		/** @var UserVariable $uv */
		$uv = UserVariable::findByData($data);
		if(!$uv){ // There can't be a measurement if there's not already a variable
			return null;
		} else{
			$m = $uv->findMeasurementIfSet($rounded);
			if($m){
				return $m->l();
			}
		}
		return null;
	}
	/**
	 * @param $data
	 * @param bool $fallback
	 * @return Measurement|null
	 */
	public static function findByData($data, bool $fallback = false): ?BaseModel{
		$id = MeasurementIdProperty::pluck($data);
		if($id){
			return static::find($id);
		}
		$variable = MeasurementVariableIdProperty::pluckRelated($data);
		if(!$variable){ // There can't be a measurement if there's not already a variable
			return null;
		}
		$rounded = MeasurementStartTimeProperty::pluckOrDefault($data);
		$userId = MeasurementUserIdProperty::pluckOrDefault($data);
		if(!$userId){le('!$userId');}
		$qb = static::whereUserAndVariable($userId, $variable->getId())
			->where(self::FIELD_START_TIME, $rounded)
			->withTrashed() // Otherwise we have SQL exception if we try to record measurement at same time
		;
		$m = $qb->first();
		return $m;
	}
	/**
	 * @param UserVariable $uv
	 */
	public function populateByUserVariable(UserVariable $uv): void{
		$this->user_variable_id = $uv->id;
		$this->user_id = $uv->user_id;
		$this->variable_category_id = $uv->getVariable()->variable_category_id;
		$this->variable_id = $uv->variable_id;
		$this->unit_id = $uv->getVariable()->default_unit_id;
	}
	public function getValueInCommonUnit(): float{
		return $this->value;
	}
	/** @noinspection PhpUnused */
	public function getUnitIdAttribute(): ?int{
		$id = $this->attributes[self::FIELD_UNIT_ID] ?? null;
		if(!$id){
			if(!isset($this->attributes[self::FIELD_VARIABLE_ID])){
				return null;
			}
			$id = $this->getVariable()->default_unit_id;
		}
		return $this->attributes[self::FIELD_UNIT_ID] = $id;
	}
	/**
	 * @return QMMeasurement
	 */
	public function getDBModel(): DBModel{
		$m = new QMMeasurementExtended();
		$m->clientId = $this->attributes[self::FIELD_CLIENT_ID] ?? null;
		$m->connectionId = $this->attributes['connection_id'] ?? null; // DON'T USE ACCESSORS HERE!  IT'S TOO SLOW!
		$m->connectorId = $this->attributes['connector_id'] ?? null;
		$m->connectorImportId = $this->attributes['connector_import_id'] ?? null;
		$m->createdAt = $this->attributes['created_at'] ?? null;
		$m->duration = $this->attributes['duration'] ?? null;
		$m->error = $this->attributes['error'] ?? null;
		$m->id = $this->attributes['id'] ?? null;
		$m->latitude = $this->attributes['latitude'] ?? null;
		$m->location = $this->attributes['location'] ?? null;
		$m->longitude = $this->attributes['longitude'] ?? null;
		$m->originalUnitId = $this->attributes['original_unit_id'] ?? null;
		$m->originalValue = $this->attributes['original_value'] ?? null;
		$m->sourceName = $this->attributes['source_name'] ?? BaseClientIdProperty::fromMemory();
		$m->startAt = db_date($this->attributes['start_at']);
		$m->startTimeEpoch = $m->startTime = $this->attributes['start_time'] ?? null;
		$m->originalStartAt = $this->attributes['original_start_at'] ?? null;
		$m->unitId = $this->attributes['unit_id'] ?? null;
		$m->updatedAt = $this->attributes['updated_at'] ?? null;
		$m->userId = $this->attributes['user_id'] ?? null;
		$m->userVariableId = $this->attributes['user_variable_id'] ?? null;
		$m->value = $this->attributes['value'] ?? null;
		$m->variableCategoryId = $this->attributes['variable_category_id'] ?? null;
		$m->variableId = $this->attributes['variable_id'];
		$m->variableName = VariableNameProperty::fromId($this->attributes['variable_id']);
		$unit = $this->getQMUnit();
		$m->unitAbbreviatedName = $unit->abbreviatedName;
		$m->unitName = $unit->name;
		if($note = $this->attributes['note'] ?? null){
			$m->setAdditionalMetaData($note);
		}
		$m->variableCategoryName = $this->getQMVariableCategory()->name;
		$m->populateDefaultFields();
		return $m;
	}
	/**
	 * @return string|object|array
	 */
	public function getNoteAttribute(){
		$val = $this->attributes[self::FIELD_NOTE] ?? null;
		if(QMStr::isJson($val)){
			return json_decode($val);
		}
		return $val;
	}
	/**
	 * @param array $raw
	 * @return Measurement
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 * @throws NoChangesException
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection
	 */
	public static function upsert(array $raw): array{
		QMLog::logStartOfProcess(__METHOD__);
		if(isset($raw['value'])){
			$raw = [$raw];
		}
		$duplicates = $newMeasurements = $byProvided = $byVariableID = $userVariables = [];
		$count = count($raw);
		foreach($raw as $one){
			ConsoleLog::info("Preparing $count measurements for upsert...");
			if($id = MeasurementIdProperty::pluckOrDefault($one)){
				/** @var Measurement $existing */
				if($existing = MeasurementIdProperty::pluckParentModel($one)){
					$existing->populate($one);
					// TODO:: Uncomment this $existing->exceptionIfNoChanges($one);
					try {
						$changed = $existing->save();
						$userVariables[$existing->variable_id] = $existing->user_variable;
						$newMeasurements[$existing->variable_id][] = $existing;
					} catch (NoChangesException $e) {
						$duplicates[] = $one;
					}
					continue;
				}
			}
			$providedVariableId = UserVariableVariableIdProperty::pluck($one);
			if($providedVariableId && isset($byProvided[$providedVariableId])){
				$uv = $byProvided[$providedVariableId];
			} else{
				$byProvided[$providedVariableId] = $uv = UserVariable::fromForeignData($one);
			}
			$userVariables[$uv->getVariableIdAttribute()] = $uv;
			$mData = $uv->newMeasurementData($one);
			$roundedAt = $mData[Measurement::FIELD_START_AT];
			$variable = $uv->getVariable();
			if($duplicate = $byVariableID[$uv->getVariableIdAttribute()][$roundedAt] ?? null){
				$currentValue = $mData[self::FIELD_VALUE];
				/** @var Measurement $duplicate */
				$duplicateValue = $duplicate[self::FIELD_VALUE];
				$aggregated = $uv->aggregateValues([$duplicateValue, $currentValue]);
				$message =
					"Duplicate $variable->name measurement at $roundedAt so using aggregated value $aggregated from $currentValue and $duplicateValue";
				QMLog::error($message);
				$mData[self::FIELD_VALUE] = $aggregated;
				$mData[self::FIELD_ERROR] = $message;
			}
			$byVariableID[$uv->getVariableIdAttribute()][$roundedAt] = $mData;
		}
		/** @var UserVariable[] $userVariables */
		foreach($byVariableID as $variableId => $arrOfMeasurements){
			$variable = Variable::findInMemoryOrDB($variableId);
			$variableName = $variable->name;
			$count = count($arrOfMeasurements);
			ConsoleLog::info("Upserting $count $variableName measurements...");
			// Can't bulk insert because it doesn't update clients Measurement::insert($arrOfMeasurements);
			foreach($arrOfMeasurements as $one){
				try {
					//QMProfile::startProfile();
					ConsoleLog::debug("Upserting measurement for {$one['value']} $variableName at {$one['start_at']}");
					$newMeasurements[$variableId][] = Measurement::upsertOne($one);
					//QMProfile::endProfileAndSaveResult();
				} catch (NoChangesException $e) {
					QMRequest::addClientWarning(__METHOD__.": ".$e->getMessage());
					$duplicates[] = $one;
				}
			}
		}
		if(count($duplicates) === count($raw)){
			throw new NoChangesException($raw);
		}
		foreach($newMeasurements as $variableId => $newForVariable){
			if(!isset($userVariables[$variableId])){
				le('!isset($userVariables[$variableId])');
			}
			$userVariables[$variableId]->updateFromMeasurements($newForVariable);
		}
		$flatten = Arr::flatten($newMeasurements, 1);
		QMLog::logEndOfProcess(__METHOD__);
		return $flatten;
	}
	/** @noinspection PhpUnused */
	/**
	 * @param $value
	 */
	public function setStartAtAttribute($value){
		if($value){
			$this->attributes[self::FIELD_START_AT] = db_date($value);
			$this->attributes[self::FIELD_START_TIME] = TimeHelper::universalConversionToUnixTimestamp($value);
		} else{
			$this->attributes[self::FIELD_START_AT] = null;
		}
		// We should do rounding when creating the measurement.
		// We can't do it here because sometimes we're populating from DB or variable is not yet set.
		//$property = $this->getPropertyModel(self::FIELD_START_AT);
		//$property->processAndSet($value);
	}
	/** @noinspection PhpUnused */
	/**
	 * @param $value
	 */
	public function setStartTimeAttribute($value){
		if($value){
			$this->attributes[self::FIELD_START_AT] = db_date($value);
			$this->attributes[self::FIELD_START_TIME] = TimeHelper::universalConversionToUnixTimestamp($value);
		} else{
			$this->attributes[self::FIELD_START_TIME] = null;
		}
		// We should do rounding when creating the measurement.
		// We can't do it here because sometimes we're populating from DB or variable is not yet set.
		//        $property = $this->getPropertyModel(self::FIELD_START_TIME);
		//        $property->processAndSet($value); // Rounds and sets start_at
	}
	/**
	 * @return string
	 */
	public function getStartAtAttribute(): string{
		$at = $this->attributes[self::FIELD_START_AT] ?? null;
		if(!$at){
			le("no at");
		} // Don't use lei here because it's slow
		return $at;
	}
	/**
	 * @param $data
	 * @return static
	 */
	public static function new($data): self {
		$uv = UserVariable::firstOrCreateByForeignData($data);
		$mData = $uv->newMeasurementData($data);
		return new Measurement($mData);
	}
	/**
	 * @param array|object $data
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	public function populate($data): void{
		$uv = $this->findOrCreateUserVariable($data);
		$mData = $uv->newMeasurementData($data);
		foreach($mData as $key => $value){
			if($value === null){
				continue;
			}
			if($key === self::CREATED_AT || $key === self::UPDATED_AT){
				continue; // Auto-generated fields
			}
			if($key === "user_variable"){
				continue;
			}
			$this->setAttribute($key, $value);
		}
	}
	/**
	 * @param $val
	 */
	public function setNoteAttribute($val){
		$this->processAndSetAttribute(static::FIELD_NOTE, $val);
	}
	/**
	 * @throws ModelValidationException
	 */
	public function getUserVariableClient(): UserVariableClient{
		$attributes = [
			UserVariableClient::FIELD_VARIABLE_ID => $this->variable_id,
			UserVariableClient::FIELD_USER_ID => $this->user_id,
			UserVariableClient::FIELD_CLIENT_ID => $this->client_id,
		];
		$uvc = UserVariableClient::where($attributes)->first();
		if(!$uvc){
			$uvc = new UserVariableClient($attributes);
		}
		$uvc->user_variable_id = $this->user_variable_id;
		$uvc->setIfGreaterThanExisting(UserVariableClient::FIELD_LATEST_MEASUREMENT_AT, $this->start_at);
		$uvc->setIfLessThanExisting(UserVariableClient::FIELD_EARLIEST_MEASUREMENT_AT, $this->start_at);
		$uvc->save();
		return $uvc;
	}
	public function getUserClient(): UserClient{
		$attributes = [
			UserVariableClient::FIELD_USER_ID => $this->user_id,
			UserVariableClient::FIELD_CLIENT_ID => $this->client_id,
		];
		$uvc = UserClient::where($attributes)->first();
		if(!$uvc){
			$uvc = new UserClient($attributes);
		}
		$uvc->setIfGreaterThanExisting(UserVariableClient::FIELD_LATEST_MEASUREMENT_AT, $this->start_at);
		$uvc->setIfLessThanExisting(UserVariableClient::FIELD_EARLIEST_MEASUREMENT_AT, $this->start_at);
		try {
			$uvc->save();
		} catch (ModelValidationException $e) {
			le($e);
		}
		return $uvc;
	}
	/**
	 * @param array $options
	 * @return bool
	 * @throws NoChangesException
	 */
	public function save(array $options = []): bool{
		try {
            if(!isset($this->attributes[self::FIELD_USER_ID])){
                $this->attributes[self::FIELD_USER_ID] = QMAuth::id();
            }
            if(!isset($this->attributes[self::FIELD_UNIT_ID])){
                $this->attributes[self::FIELD_UNIT_ID] = $this->getVariable()->default_unit_id;
            }
            if(!isset($this->attributes[self::FIELD_USER_VARIABLE_ID])){
                $this->attributes[self::FIELD_USER_VARIABLE_ID] = $this->getUserVariable()->id;
            }
            if(!isset($this->attributes[self::FIELD_ORIGINAL_START_AT])){
                $this->attributes[self::FIELD_ORIGINAL_START_AT] = $this->attributes[self::FIELD_START_AT];
            }
            if(!isset($this->attributes[self::FIELD_ORIGINAL_UNIT_ID])){
                $this->attributes[self::FIELD_ORIGINAL_UNIT_ID] = $this->attributes[self::FIELD_UNIT_ID];
            }
            if(!isset($this->attributes[self::FIELD_ORIGINAL_VALUE])){
                $this->attributes[self::FIELD_ORIGINAL_VALUE] = $this->attributes[self::FIELD_VALUE];
            }
            if(!isset($this->attributes[self::FIELD_START_TIME])){
                $this->attributes[self::FIELD_START_TIME] = time_or_exception($this->attributes[self::FIELD_START_AT]);
            }
            if(!isset($this->attributes[self::FIELD_VARIABLE_CATEGORY_ID])){
                $this->attributes[self::FIELD_VARIABLE_CATEGORY_ID] = $this->getVariable()->variable_category_id;
            }
			$res = parent::save($options);
			if(!$res){
				throw new NoChangesException($this->attributes, $this);
			}
		} catch (ModelValidationException $e) {
			le($e);
		}
		$qmUV = $this->getQMUserVariable();
		$qmUV->addSavedMeasurement($this);
		return $res;
	}
    public function getTitleAttribute(): string
    {
		if(!$this->attributes){
			return static::getClassTitlePlural();
		}
        return $this->appends['title'] =
            $this->value . ' ' . $this->getUnit()->abbreviated_name. ' ' . $this->getVariable()->getTitleAttribute();
    }

    public function getDate(): string{
		return TimeHelper::YYYYmmddd($this->start_at);
	}
	public function logValue(){
		$unit = $this->getQMUnit();
		$this->logInfo("$this->value $unit->abbreviatedName at $this->start_at");
	}
	public function getAt(): string{
		return $this->getStartAtAttribute();
	}
	public function getUniqueNamesSlug(): string{
		return $this->getUserVariable()->getUniqueNamesSlug() . "-" . $this->getStartAtAttribute();
	}
	/**
	 * @return string
	 * Much faster than getUniqueNamesSlug
	 */
	public function getSlugFromMemory(): string{
		$u = $this->getUserFromMemory();
		$uv = $this->getUserVariableFromMemory();
		if($u && $uv){
			return $this->getUniqueNamesSlug();
		}
		return $this->getUniqueIndexIdsSlug();
	}
	/**
	 * @param array $values
	 * @param UserVariable $uv
	 * @return Measurement[]
	 */
	public static function bulkInsert(array $values, UserVariable $uv): array{
		$uv->logInfo("Bulk inserting " . count($values) . "measurements.  \n\t" . $uv->getUrl());
		try {
            static::insert($values);
		} catch (\Throwable $e) {
		    $needToSave = true;
		}
		$measurements = [];
		$secs = $uv->getMinimumAllowedSecondsBetweenMeasurements();
		foreach($values as $one){
			if(isset($one[self::FIELD_START_TIME])){
				$one[self::FIELD_START_TIME] = Stats::roundToNearestMultipleOf($one[self::FIELD_START_TIME], $secs);
				$one[self::FIELD_START_AT] = db_date($one[self::FIELD_START_TIME]);
			} else{
				$one[self::FIELD_START_AT] = TimeHelper::roundToNearestXSeconds($one[self::FIELD_START_AT], $secs);
				$one[self::FIELD_START_TIME] = strtotime($one[self::FIELD_START_AT]);
			}
			$m = new Measurement();
			$m->forceFill($one);
			$measurements[] = $m;
		}
		$qmUV = $uv->getQMUserVariable();
		foreach($measurements as $m){
			$qmUV->addSavedMeasurement($m);
		}
		$uv->updateFromMeasurements($measurements);
		return $measurements;
	}
	/**
	 * @param array $values
	 * @param UserVariable $uv
	 * @return Measurement[]
	 */
	public static function bulkInsertIfPossible(array $values, UserVariable $uv): array{
		try {
			return Measurement::bulkInsert($values, $uv);
		} catch (QueryException $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
			return self::saveChanged($values, $uv);
		}
	}
	/**
	 * @param \Illuminate\Database\Query\Builder|Builder $qb
	 * @param null $user
	 * @return \Illuminate\Database\Query\Builder
	 */
	public function restrictQueryBasedOnPermissions($qb, $user = null): \Illuminate\Database\Query\Builder{
		if(!$user){
			$user = QMAuth::getQMUser();
		}
		if($qb instanceof Builder){
			$qb = $qb->getQuery();
		}
		if(!$user && !AppMode::isApiRequest()){
			return $qb;
		}
		if(QMAuth::isAdmin()){
			return $qb;
		}
		$userId = UserIdProperty::fromRequest();
		$variableId = VariableIdProperty::fromRequest();
		if($userId && $variableId){
			$uv = UserVariable::findByVariableId($variableId, $userId);
			if($uv && $uv->is_public){
				return $qb;
			}
		}
		return parent::restrictQueryBasedOnPermissions($qb);
	}
	public function getVariableCategoryId(): int{
		return $this->attributes[self::FIELD_VARIABLE_CATEGORY_ID];
	}
	public function hasVariableCategoryId(): bool{
		return $this->attributes[self::FIELD_VARIABLE_ID] ?? false;
	}
	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_USER_ID];
	}
	/**
	 * @return mixed
	 */
	public function getDBValue(){
		return $this->attributes[self::FIELD_VALUE] ?? null;
	}
	/**
	 * @param null $reader
	 * @return bool
	 */
	public function canReadMe($reader = null): bool{
		if(parent::canReadMe($reader)){
			return true;
		}
		return $this->getUser()->share_all_data;
	}
	/**
	 * @return Avatar
	 */
	public function image(): Avatar{
		return Avatar::make('', function(){
			return $this->getImage();
		})->disk('public')->maxWidth(50)->disableDownload()->thumbnail(function(){
			return $this->getImage();
		})->preview(function(){
			return $this->getImage();
		});
	}
	/**
	 * @return Text
	 */
	public function valueDisplay(): Text{
		$value = Text::make('Value', Measurement::FIELD_VALUE, function(){
			$val = Stats::roundToSignificantFiguresIfGreater($this->value, 4);
			$str = (string)$val;
			if(strlen($str) > 6){
				$val = Stats::roundToSignificantFiguresIfGreater($this->value, 4);
			}
			return $str;
		})->sortable()->updateLink();
		return $value;
	}
	/**
	 * @return Number
	 */
	public function valueEdit(): Number{
		$fields = Number::make('Value', Measurement::FIELD_VALUE)->rules('required');
		return $fields;
	}
	/**
	 * @return Text
	 */
	public function unitAbbreviatedName(): Text{
		return Text::make('Unit', function(){
			return $this->getUnit()->abbreviated_name;
		})->onlyOnIndex();
	}
	/**
	 * @param null $request
	 * @return ID
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function idField($request = null): ID{
		return ID::make()->hideFromIndex()->readonly()->sortable();
	}
	/**
	 * @return \App\Fields\BelongsTo
	 */
	public function unitEdit(): \App\Fields\BelongsTo{
		return \App\Fields\BelongsTo::make('Unit', 'unit', UnitBaseAstralResource::class)->readonly(function(){
			return $this->exists;
		})->required()->withoutTrashed();
	}
	/**
	 * @return DateTime
	 */
	public function startTime(): DateTime{
		$fields = DateTime::make('Date', Measurement::FIELD_START_AT)->format('LT ll')->sortable()->rules('required');
		return $fields;
	}
	/**
	 * Determine if this resource is available for navigation.
	 * @param Request $request
	 * @return bool
	 */
	public static function availableForNavigation(Request $request): bool{
		return true;
	}
	public function getVariableIdAttribute(): ?int{
		$id = $this->attributes['variable_id'] ?? null;
		if($id){
			return $id;
		}
		if($this->relationLoaded('variable')){
			return $this->variable->id;
		}
		return null;
	}
    public function connector(): BelongsTo{
        if($this->connector_id && !$this->relationLoaded('connector')){
            $connector = Connector::findInMemoryOrDB($this->connector_id);
            $this->setRelation('connector', $connector);
        }

        return $this->belongsTo(Connector::class, Measurement::FIELD_CONNECTOR_ID, Connector::FIELD_ID,
            Measurement::FIELD_CONNECTOR_ID);
    }
	public static function savePostData(array $data){
		$uv = UserVariable::fromData($data);
		$measurements = $uv->saveMultipleMeasurements($data['measurements']);
		return $measurements;
	}
}
