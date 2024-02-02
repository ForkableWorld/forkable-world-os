<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables;
use App\Buttons\States\VariableSettingsStateButton;
use App\Models\BaseModel;
use App\Models\Measurement;
use App\Models\TrackingReminder;
use App\Models\TrackingReminderNotification;
use App\Models\Unit;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Products\AmazonHelper;
use App\Properties\Base\BaseImageUrlProperty;
use App\Properties\Variable\VariableNameProperty;
use App\Properties\Variable\VariableSynonymsProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\DBModel;
use App\Slim\Model\QMUnit;
use App\Slim\View\Request\Variable\GetCommonVariablesRequest;
use App\Slim\View\Request\Variable\GetUserVariableRequest;
use App\Storage\Memory;
use App\Traits\HasModel\HasVariable;
use App\Types\ObjectHelper;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Utils\UrlHelper;
use App\VariableCategories\InvestmentStrategiesVariableCategory;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
class VariableSearchResult extends DBModel {
	use HasVariable;
	public $commonAlias;
	protected $commonUnitId;
	protected ?QMUnit $commonUnit = null;
	protected $unit;
	protected $unitId;
	protected $variableCategory;
	protected $variableCategoryId;
	protected $variableName;
	protected $slug;
	public $causeOnly;
	public $commonOptimalValueMessage;
	public $displayName;
	public $displayNameWithCategoryOrUnitSuffix;
	public $id;
	public $imageUrl;
	public $ionIcon;
	public $inputType;
	public $latestTaggedMeasurementTime;
	public $latestTaggedMeasurementStartAt;
	public $manualTracking;
	public $name;
	public $numberCommonTaggedBy;
	public $numberOfGlobalVariableRelationshipsAsCause;
	public $numberOfGlobalVariableRelationshipsAsEffect;
	public $numberOfCommonTags;
	public $numberOfCorrelations;
	public $numberOfCorrelationsAsCause;
	public $numberOfCorrelationsAsEffect;
	public $numberOfRawMeasurements;
	public $numberOfMeasurements;
	public $numberOfTrackingReminders;
	public $numberOfUniqueValues;
	public $numberOfUserVariableRelationshipsAsCause;
	public $numberOfUserVariableRelationshipsAsEffect;
	public $numberOfUserVariables;
	public $outcome;
	public $pngUrl;
	public $predictor;
	public $productUrl;
	public $isPublic;
	public $sortingScore;
	public $subtitle;
	public $svgUrl;
	public $synonyms;
	public $unitAbbreviatedName;
	public $variableCategoryName;
	public $variableId;
	public $url;
	public const FIELD_OUTCOME = 'outcome';
	public const LARAVEL_CLASS = Variable::class;
	/**
	 * VariableSearchResult constructor.
	 * @param null $row
	 */
	public function __construct($row = null){
		if($row){
			$this->populateFieldsByArrayOrObject($row);
			$this->populateDefaultFields();
		}
	}
	public function populateDefaultFields(){
		$this->setNameAndDisplayName();
		$this->setVariableCategoryId($this->getVariableCategoryId());
		$this->getUnitAbbreviatedName();
		$this->setIonIcon();
		$this->getSvgUrl();
		$this->getPngUrl();
		$this->getNumberOfCorrelationsAsCause();
		$this->getNumberOfCorrelationsAsEffect();
		$this->setBooleanProperties();
		$this->setSortingScore();
		$this->getProductUrl(); // Replaces old affiliate id's
		$this->getOrSetCauseOnly();
		$this->url = $this->getUrl();
	}
	public function getUrl(array $params = []): string{
		$url = static::getIndexUrl() . "/" . $this->getSlug();
		return $this->url = UrlHelper::addParams($url, $params);
	}
	public static function getIndexPath(): string{
		return static::getPluralizedSlugifiedClassName();
	}
	public static function getIndexUrl(array $params = []): string{
		return qm_url(static::getIndexPath(), $params);
	}
	/**
	 * @param int $variableCategoryId
	 */
	public function setVariableCategoryId(int $variableCategoryId): void{
		$this->variableCategoryId = $variableCategoryId;
	}
	protected function setNameAndDisplayName(): void{
		$this->setName($this->variableName ?? $this->name);
		if($this->name !== $this->variableName){
			le('$this->name !== $this->variableName');
		}
		$dn = VariableNameProperty::variableToDisplayName($this);
		if(!$dn){
			$dn = VariableNameProperty::variableToDisplayName($this);
		}
		$this->setDisplayName($dn);
		if($this->name !== $this->variableName){
			le('$this->name !== $this->variableName');
		}
	}
	protected function setBooleanProperties(){
		if($this->causeOnly !== null){
			$this->setCauseOnly($this->causeOnly);
		}
		$this->getManualTracking();
		if(!is_null($this->outcome)){
			$this->setOutcome((bool)$this->outcome);
		}
	}
	/**
	 * @param mixed $causeOnly
	 */
	private function setCauseOnly($causeOnly){
		$this->causeOnly = (bool)$causeOnly;
	}
	/**
	 * @param bool $outcome
	 * @return bool
	 */
	protected function setOutcome(?bool $outcome): ?bool{
		if($outcome === false && $this->variableCategoryId === InvestmentStrategiesVariableCategory::ID){
			le('$outcome === false && $this->variableCategoryId === InvestmentStrategiesVariableCategory::ID');
		}
		return $this->outcome = $outcome;
	}
	/**
	 * @return int
	 */
	public function getVariableIdAttribute(): ?int{
		return $this->variableId ?? $this->id;
	}
	/**
	 * @return string
	 */
	protected function getVariableSettingsUrl(): string{
		return VariableSettingsStateButton::getVariableSettingsUrlForVariableId($this->getVariableIdAttribute());
	}
	/**
	 * @return string
	 */
	public function getVariableCategoryName(): string{
		return $this->variableCategoryName = $this->getQMVariableCategory()->name;
	}
	/**
	 * @return string[]
	 */
	public function getSynonymsAttribute(): array{
		return $this->synonyms  = $this->getVariable()->getSynonymsAttribute();
	}
	public function getDisplayNameWithCategoryOrUnitSuffix(): string{
		if($this->displayNameWithCategoryOrUnitSuffix){
			return $this->displayNameWithCategoryOrUnitSuffix;
		}
		return $this->displayNameWithCategoryOrUnitSuffix =
			VariableNameProperty::addSuffix($this->getOrSetVariableDisplayName(), $this->getCommonUnit(), false,
				$this->getQMVariableCategory());
	}
	/**
	 * @return string
	 */
	public function getOrSetVariableDisplayName(): string{
		if(!$this->displayName){
			$this->setDisplayName(VariableNameProperty::variableToDisplayName($this));
		}
		return $this->displayName;
	}
	/**
	 * @return string
	 */
	public function setSvgUrl(): string{
		if(!$this->svgUrl && $this->getQMVariableCategory()){
			$this->svgUrl = $this->getQMVariableCategory()->getSvgUrl();
		}
		return $this->svgUrl;
	}
	/**
	 * @return string
	 */
	public function getPngUrl(): string{
		if($url = $this->pngUrl){
			return $url;
		}
		$imageUrl = ObjectHelper::get($this, ['variableImageUrl', 'imageUrl']);
		if($imageUrl && stripos($imageUrl, '.png') !== false){
			return $this->setPngUrl($imageUrl);
		}
		$url = $this->getQMVariableCategory()->getPngUrl();
		return $this->setPngUrl($url);
	}
	/**
	 * @return string
	 */
	public function setIonIcon(): string{
		if(!$this->ionIcon && $this->getQMVariableCategory()){
			$this->ionIcon = $this->getQMVariableCategory()->getIonIcon();
		}
		return $this->ionIcon;
	}
	/**
	 * @return QMVariableCategory
	 */
	public function getQMVariableCategory(): QMVariableCategory{
		$category = $this->variableCategory;
		if($category){
			return $this->variableCategory = QMVariableCategory::instantiateIfNecessary($category);
		}
		$id = $this->getVariableCategoryId();
		$cat = QMVariableCategory::find($id);
		$this->variableCategoryName = $cat->name;
		$this->variableCategory = $cat;
		QMVariableCategory::addVariableCategoryNamesToObject($this);
		return $this->variableCategory;
	}
	/**
	 * @return int
	 */
	public function getNumberOfCorrelationsAsCause(): ?int{
		$num = $this->numberOfCorrelationsAsCause;
		if($num !== null){
			return $num;
		}
		$ac = $this->getNumberOfGlobalVariableRelationshipsAsCause();
		$uc = $this->numberOfUserVariableRelationshipsAsCause;
		return $this->numberOfCorrelationsAsCause = $ac + $uc;
	}
	/**
	 * @return int
	 */
	public function getNumberOfCorrelationsAsEffect(): ?int{
		if($num = $this->numberOfCorrelationsAsEffect){
			return $num;
		}
		$ac = $this->getNumberOfGlobalVariableRelationshipsAsEffect();
		$uc = $this->numberOfUserVariableRelationshipsAsEffect;
		return $this->numberOfCorrelationsAsEffect = $ac + $uc;
	}
	/**
	 * @return bool
	 */
	public function getManualTracking(): ?bool{
		if($this->manualTracking !== null){
			return $this->manualTracking  = (bool)$this->manualTracking;
		}
		$val = $this->getVariable()->getManualTrackingAttribute();
		return $this->manualTracking = $val;
	}
	/**
	 * @return QMUnit
	 */
	public function getUserOrCommonUnit(): QMUnit{
		if($u = $this->unit){
			return $u;
		}
		$id = $this->getUnitIdAttribute();
		return $this->unit = QMUnit::find($id);
	}
	public function getUnit(): Unit{
		return $this->getUserOrCommonUnit()->l();
	}
	/**
	 * @return int
	 */
	public function getUnitIdAttribute(): ?int{
		$id = ObjectHelper::get($this, [
			'unitId',
			'userUnitId',
			'commonUnitId',
			'defaultUnitId',
		]);
		if(!$id){
			$id = $this->getAttribute(Variable::FIELD_DEFAULT_UNIT_ID);
		}
		lei(!$id, "No unit id: $id");
		return $this->unitId = $id;
	}
	/**
	 * @return mixed
	 */
	public function getUnitAbbreviatedName(): string{
		if($n = $this->unitAbbreviatedName){
			return $n;
		}
		return $this->setUnitAbbreviatedName($this->getUserOrCommonUnit()->abbreviatedName);
	}
	/**
	 * @return string
	 */
	public function getSubtitleAttribute(): string{
		return $this->subtitle;
	}
	/**
	 * @param string $subtitle
	 */
	public function setSubtitle(string $subtitle){
		$d = $this->getOrSetVariableDisplayName();
		$n = $this->getVariableName();
		if(strtolower($n) !== strtolower($d)){
			$nonDisplayNameRemainder = trim(str_replace($d, '', $n));
			//if(strpos($s, '(') === false){$s = "(".$s.")";}
			if(stripos($subtitle, $nonDisplayNameRemainder) === false){
				$subtitle = $subtitle . " " . $nonDisplayNameRemainder;
			}
		}
		//if(AppMode::isDevelopment()){$subtitle = $subtitle . " ".$this->getVariableId();}
		$this->subtitle = $subtitle;
	}
	public function setStudiesSubtitle(){
		$this->setSubtitle("$this->numberOfCorrelations studies");
	}
	public function setMeasurementsSubtitle(){
		$num = $this->getNumberOfMeasurements();
		$this->setSubtitle("$num measurements");
	}
	public function setUserVariablesSubtitle(){
		if($this->numberOfUserVariables < 10){
			$this->setSubtitle($this->getVariableCategoryName());
		} else{
			$this->setSubtitle("$this->numberOfUserVariables trackers");
		}
	}
	/**
	 * @param QMVariable[] $variables
	 * @return array
	 */
	public static function convertVariablesToSearchResults(array $variables): array{
		$search = [];
		foreach($variables as $variable){
			$v = new VariableSearchResult($variable);
			$search[] = $v;
		}
		return $search;
	}
	/**
	 * @param array $params
	 * @return array|bool|Builder[]|Collection|DBModel[]|VariableSearchResult[]
	 */
	public static function get(array $params = []): array{
		$user = QMAuth::getQMUser();
		if($user){
			$qb = GetUserVariableRequest::qb();
			$qb->where(UserVariable::TABLE . '.' . UserVariable::FIELD_USER_ID, $user->getId());
			$qb->orderBy(UserVariable::TABLE . '.' . UserVariable::FIELD_LATEST_TAGGED_MEASUREMENT_START_AT, 'DESC');
		} else{
			$qb = GetCommonVariablesRequest::getBaseQB();
			$qb->orderBy(UserVariable::TABLE . '.' . Variable::FIELD_NUMBER_OF_USER_VARIABLES, 'DESC');
		}
		$qb->limit(100);
		$rows = $qb->getArray();
		return $rows;
		//$variables = self::convertRowsToModels($rows, true);
		//return $variables;
	}
	/**
	 * @return int
	 */
	public function getNumberOfUserVariables(): ?int{
		return $this->numberOfUserVariables;
	}
	/**
	 * @return int
	 */
	public function getNumberOfTrackingReminders(): ?int{
		return $this->numberOfTrackingReminders;
	}
	/**
	 * @return mixed
	 */
	public function getProductUrl(){
		$this->productUrl = AmazonHelper::replaceOldAffiliateId($this->productUrl);
		return $this->productUrl;
	}
	/**
	 * @return int
	 */
	public function getNumberOfGlobalVariableRelationshipsAsEffect(): ?int{
		$num = $this->numberOfGlobalVariableRelationshipsAsEffect ?? $this->dbRow->numberOfCorrelationsAsEffect ??
			$this->numberOfCorrelationsAsEffect ??
			$this->getAttributeFromVariableIfSet(Variable::FIELD_NUMBER_OF_AGGREGATE_CORRELATIONS_AS_EFFECT);
		return $this->numberOfGlobalVariableRelationshipsAsEffect = $num;
	}
	/**
	 * @return int
	 */
	public function getNumberOfGlobalVariableRelationshipsAsCause(): ?int{
		$num = $this->numberOfGlobalVariableRelationshipsAsCause ?? $this->dbRow->numberOfCorrelationsAsCause ??
			$this->numberOfCorrelationsAsCause ??
			$this->getAttributeFromVariableIfSet(Variable::FIELD_NUMBER_OF_AGGREGATE_CORRELATIONS_AS_CAUSE);
		return $this->numberOfGlobalVariableRelationshipsAsCause = $num;
	}
	/**
	 * @param string $attribute
	 * @return mixed|null
	 */
	public function getAttributeFromVariableIfSet(string $attribute){
		$l = $this->laravelModel;
		if($l instanceof Variable){
			return $l->getAttribute($attribute);
		} elseif($l){
			/** @var UserVariable|TrackingReminder|TrackingReminderNotification $l */
			return $l->getVariable()->getAttribute($attribute);
		}
		return null;
	}
	/**
	 * @return int
	 */
	public function getNumberOfGlobalVariableRelationships(): ?int{
		$c = $this->getNumberOfGlobalVariableRelationshipsAsCause();
		$e = $this->getNumberOfGlobalVariableRelationshipsAsEffect();
		return $this->numberOfCorrelations = $c + $e;
	}
	/**
	 * @return float
	 */
	public function getSortingScore(): float{
		return $this->sortingScore ?: $this->setSortingScore();
	}
	/**
	 * @param int $userId
	 * @return QMUserVariable
	 */
	public function findQMUserVariable(int $userId): QMUserVariable{
		if(!$userId && isset($this->userId)){
			$userId = $this->userId;
		}
		if(!$userId){
			if(!QMAuth::getQMUserIfSet()){
				$this->throwLogicException("No user provided to QMVariable::getUserVariable");
			}
			$userId = QMAuth::id();
		}
		$v = QMUserVariable::findOrCreateByNameOrId($userId, $this->getVariableIdAttribute(), []);
		return $v;
	}
	/**
	 * @param int $id
	 * @return QMCommonVariable|null
	 */
	public static function getVariableConstantsById(int $id): ?QMCommonVariable{
		$constants = QMCommonVariable::getHardCodedVariables();
		foreach($constants as $v){
			if(!$v->variableName){
				le('!$v->variableName');
			}
			if($v->getVariableIdAttribute() === $id){
				return $v;
			}
		}
		return null;
	}
	/**
	 * @return string
	 */
	public function __toString(){
		return $this->name;
	}
	public function getSettingsUrl(): string{
		return $this->getVariableSettingsUrl();
	}
	public function getViewSmallMeasurementsUrl(): string{
		$min = $this->getMinimumAllowedValueAttribute();
		$commonUnit = $this->getCommonUnit();
		if($min === null || $min === -1){
			le("No minimum for $this getViewSmallMeasurementsUrl!");
		}
		return Measurement::generateDataLabIndexUrl([
			Measurement::FIELD_VARIABLE_ID => $this->getVariableIdAttribute(),
			Measurement::FIELD_VALUE => "(lt)$min",
			Measurement::FIELD_UNIT_ID => $commonUnit->id,
			"title" => "too small",
		]);
	}
	public function getOrCreateUserVariable(int $userId): QMUserVariable{
		return QMUserVariable::getOrCreateById($userId, $this->getVariableIdAttribute());
	}
	/**
	 * @return string
	 */
	public function getIonIcon(): string{
		return $this->ionIcon ?: $this->setIonIcon();
	}
	/**
	 * @return string
	 */
	public function getSvgUrl(): string{
		return $this->svgUrl ?: $this->setSvgUrl();
	}
	public function getOrSetCauseOnly(): bool{
		if($this->causeOnly !== null){
			return $this->causeOnly;
		}
		return $this->causeOnly = $this->getAttributeFromVariableOrCategory(Variable::FIELD_CAUSE_ONLY);
	}
	/**
	 * @return int
	 */
	public function getNumberOfCorrelations(): ?int{
		if($num = $this->numberOfCorrelations){
			return $num;
		}
		$asCause = $this->getNumberOfCorrelationsAsCause();
		$asEffect = $this->getNumberOfCorrelationsAsEffect();
		return $this->numberOfCorrelations = $asCause + $asEffect;
	}
	public function getTitleAttribute(): string{
		if(!$this->displayName){
			$this->setDisplayName(VariableNameProperty::variableToDisplayName($this));
		}
		return $this->displayName;
	}
	/**
	 * @param string $name
	 * @return string
	 */
	public function setVariableName(string $name): string{
		return $this->name = $this->variableName = $name;
	}
	/**
	 * @param string $pngUrl
	 * @return string
	 */
	public function setPngUrl(string $pngUrl): string{
		return $this->pngUrl = $pngUrl;
	}
	public function getVariable(): Variable{
		if($this->laravelModel instanceof Variable){
			return $this->laravelModel;
		}
		$id = $this->getVariableIdAttribute();
		$v = Variable::findInMemoryOrDB($id);
		if(!$v){le("No variable with id $id");}
		return $v;
	}
	/**
	 * @param string $unitAbbreviatedName
	 * @return string
	 */
	public function setUnitAbbreviatedName(string $unitAbbreviatedName): string{
		return $this->unitAbbreviatedName = $unitAbbreviatedName;
	}
	/**
	 * @param int|null $num
	 * @return int|null
	 */
	public function setNumberCommonTaggedBy(?int $num): ?int{
		$previous = $this->numberCommonTaggedBy;
		if($num !== null && $num !== $previous){
			$this->logDebug("Changed numberCommonTaggedBy from $previous to $num");
			$this->numberCommonTaggedBy = $num;
		}
		return $num;
	}
	/**
	 * @param int $num
	 * @return int|null
	 */
	public function setNumberOfCommonTags(?int $num): ?int{
		$previous = $this->numberOfCommonTags;
		if($num !== null && $num !== $previous){
			$this->logDebug("Changed NumberOfCommonTags from $previous to $num");
			$this->numberOfCommonTags = $num;
		}
		return $num;
	}
	/**
	 * @param string $name
	 * @return string
	 */
	public function setName(string $name): string{
		return $this->setVariableName($name);
	}
	/**
	 * @param string $displayName
	 */
	public function setDisplayName(string $displayName): void{
		$this->displayName = $displayName;
	}
	/**
	 * @return QMCommonVariable
	 */
	public function getCommonVariable(): QMCommonVariable{
		/** @var QMCommonVariable $cv */
		if($this instanceof QMCommonVariable){
			$this->l()->assertHasStatusAttribute();
			return $this;
		}
		$cv = QMCommonVariable::find($this->getVariableIdAttribute());
		$cv->l()->assertHasStatusAttribute();
		return $cv;
	}
	/**
	 * @param Variable $v
	 */
	public function populateByLaravelVariable(Variable $v): void{
		// From Variable
		$this->bestGlobalVariableRelationshipId = $v->best_global_variable_relationship_id;
		$this->commonAdditionalMetaData = $v->additional_meta_data;
		$this->commonAlias = $v->common_alias;
		$this->commonBestCauseVariableId = $v->best_cause_variable_id;
		$this->commonBestEffectVariableId = $v->best_effect_variable_id;
		$this->commonMostCommonConnectorId = $v->most_common_connector_id;
		if($v->number_of_raw_measurements !== null){
			$this->commonNumberOfRawMeasurements = $v->number_of_raw_measurements;
		}
		$this->setCommonOptimalValueMessage($v->optimal_value_message);
		$u = $this->commonUnit = $v->getQMUnit();
		$this->commonUnitId = $v->default_unit_id;
		$this->setDisplayName($v->getTitleAttribute());
		$this->setImageUrl($imageUrl = $v->image_url);
		if(strpos($imageUrl, '.png')){
			$this->setPngUrl($imageUrl);
		}
		$this->ionIcon = $v->ion_icon;
		$this->manualTracking = $v->manual_tracking;
		$this->maximumAllowedDailyValue = $v->maximum_allowed_daily_value;
		$this->setMaximumAllowedValue($v->maximum_allowed_value ?? $u->getMaximumValue());
		$minimumValue = $u->getMinimumValue();
		$this->setMinimumAllowedValue($v->minimum_allowed_value ?? $minimumValue);
		$this->setVariableName($v->name);
		$this->setNumberCommonTaggedBy($v->number_common_tagged_by);
		$this->numberOfGlobalVariableRelationshipsAsCause = $v->number_of_global_variable_relationships_as_cause;
		$this->numberOfGlobalVariableRelationshipsAsEffect = $v->number_of_global_variable_relationships_as_effect;
		$this->setNumberOfCommonTags($v->number_of_common_tags);
		$this->numberOfUserVariables = $v->number_of_user_variables;
		$this->setOutcome($v->outcome); // Too complicated to use user variable outcomes
		if($v->price !== null){$this->price = $v->price;}
		$this->productUrl = $v->product_url;
		$this->secondMostCommonValue = $v->second_most_common_value;
		$this->synonyms = $v->getSynonymsAttribute();
		$this->thirdMostCommonValue = $v->third_most_common_value;
		$this->wikipediaUrl = $v->wikipedia_url;
		$this->setImageUrl($v->getImage());
		$this->url = $this->getUrl();
		if(!$this->unitAbbreviatedName){
			$this->unitAbbreviatedName = $v->getQMUnit()->getAbbreviatedName();
		}
		$this->clientId = $v->client_id;
	}
	/**
	 * @param string $imageUrl
	 * @param bool $updateIfDifferent
	 * @throws \App\Exceptions\InvalidStringException
	 */
	public function setImageUrl(string $imageUrl, bool $updateIfDifferent = false): string{
		if(!empty($this->imageUrl) && !$updateIfDifferent){
			$this->logDebug("imageUrl already set to $this->imageUrl so not setting to provided imageUrl $imageUrl");
			return $imageUrl;
		}
		if($imageUrl){
			BaseImageUrlProperty::assertIsImageUrl($imageUrl, "imageUrl");
			$imageUrl = QMStr::validateUrlAndAddHttpsIfNecessary($imageUrl, true);
			if($updateIfDifferent){
				$this->updateCommonVariableProperty(Variable::FIELD_IMAGE_URL, $imageUrl, __METHOD__);
			}
		}
		return $this->imageUrl = $imageUrl;
	}
	/**
	 * @param string $field
	 * @param $value
	 * @param string $method
	 * @return mixed
	 */
	protected function updateCommonVariableProperty(string $field, $value, string $method){
		$camel = QMStr::toCamelCase($field);
		$this->$camel = $value;
		if(Memory::currentTaskIsCommonVariableAnalysis()){
			return $value;
		} // We'll do this in the full update function
		$cv = $this->getVariable();
		$cv->setAttribute($field, $value);
		$dirty = $cv->getDirty();
		if($dirty){
			$cv->save();
		}
		return $value;
	}
	/**
	 * @param Variable $l
	 */
	public function populateByLaravelModel(BaseModel $l){
		$this->setLaravelModel($l);
		$this->populateByLaravelVariable($l);
		$arr = $l->attributesToArray();
		$arr = QMArr::removeNulls($arr);
		foreach($arr as $dbFieldName => $newValue){
			$propertyName = static::getPropertyNameForDbField($dbFieldName);
			if($propertyName === ""){
				le('$propertyName === ""');
			}
			if(!$propertyName){
				continue;
			}
			if(isset($this->$propertyName)){
				continue;
			}
			$this->$propertyName = $newValue;
		}
		$this->variableId = $this->id = $l->id;
	}
	/**
	 * @param mixed $commonOptimalValueMessage
	 */
	public function setCommonOptimalValueMessage($commonOptimalValueMessage): void{
		$this->commonOptimalValueMessage = $commonOptimalValueMessage;
	}
	public function getFontAwesome(): string{
		return $this->getQMVariableCategory()->getFontAwesome();
	}
	public function getVariableCategoryId(): int{
		if(!$this->variableCategoryId){
			$this->variableCategoryId = $this->commonVariableCategoryId;
		}
		if(!$this->variableCategoryId){
			le("no cat id", $this);
		}
		return $this->variableCategoryId;
	}
	public function getNameAttribute(): string{
		return $this->getVariableName();
	}
	/**
	 * @param int|null $number
	 */
	public function setNumberOfMeasurements(?int $number): void{
		$this->numberOfRawMeasurements = $this->numberOfMeasurements = $number;
	}
}
