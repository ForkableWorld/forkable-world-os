<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables;
use App\Buttons\States\OnboardingStateButton;
use App\Buttons\VariableButton;
use App\CodeGenerators\Swagger\SwaggerDefinition;
use App\VariableRelationships\QMVariableRelationship;
use App\Exceptions\VariableCategoryNotFoundException;
use App\Files\FileHelper;
use App\Files\PHP\PhpClassFile;
use App\Files\PHP\VariableCategoryFile;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Models\Variable;
use App\Models\VariableCategory;
use App\Properties\Base\BaseValenceProperty;
use App\Slim\Model\DBModel;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\QMUnit;
use App\Slim\Model\Reminders\QMTrackingReminder;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Storage\Memory;
use App\Traits\HardCodable;
use App\Traits\HasButton;

use App\Traits\HasName;
use App\Traits\HasOptions;
use App\Traits\HasSynonyms;
use App\Types\ObjectHelper;
use App\Types\QMArr;
use App\Types\QMStr;
use App\UI\FontAwesome;
use App\Utils\APIHelper;
use App\Utils\IonicHelper;
use App\VariableCategories\ActivitiesVariableCategory;
use App\VariableCategories\EmotionsVariableCategory;
use App\VariableCategories\FoodsVariableCategory;
use App\VariableCategories\GoalsVariableCategory;
use App\VariableCategories\LocationsVariableCategory;
use App\VariableCategories\MiscellaneousVariableCategory;
use App\VariableCategories\NutrientsVariableCategory;
use App\VariableCategories\PaymentsVariableCategory;
use App\VariableCategories\PhysicalActivityVariableCategory;
use App\VariableCategories\SleepVariableCategory;
use App\VariableCategories\SoftwareVariableCategory;
use App\VariableCategories\SymptomsVariableCategory;
use App\VariableCategories\TreatmentsVariableCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use App\Fields\HasMany;
use LogicException;
/**
 * @mixin VariableCategory
 */
abstract class QMVariableCategory extends DBModel {
    use HardCodable, HasSynonyms;
	use HasButton,
		HasName, HasOptions;
    public const ID = null;
    private static $synonymsMap;
    private static $variableCategoriesIndexedById;
    public $amazonProductCategory;
    public float $averageSecondsBetweenMeasurements;
    public $boring;
    public $causeOnly;
    public $combinationOperation;
    public $common;
    public $defaultUnitAbbreviatedName;
    public $defaultUnitId;
    public $durationOfAction;
    public $effectOnly;
    public $fillingType;
    public ?float $fillingValue;
    public $fontAwesome;
    public $imageUrl;
    public $ionIcon;
    public $isPublic;
    public $manualTracking;
    public $maximumAllowedValue;
    public $medianSecondsBetweenMeasurements;
    public $minimumAllowedSecondsBetweenMeasurements;
    public $minimumAllowedValue;
    public $moreInfo;
    public $name;
    public $nameSingular;
    public $numberOfMeasurements;
    public $numberOfOutcomeCaseStudies;
    public $numberOfOutcomePopulationStudies; // Don't remove this or you get undefined error from getDBInsertionArray
    public $numberOfPredictorCaseStudies;
    public $numberOfPredictorPopulationStudies; // Don't remove this or you get undefined error from getDBInsertionArray
    public $numberOfUserVariables;
    public $numberOfVariables;
    public $onsetDelay;
    public $outcome;
    public $pngPath;
    public $pngUrl;
    public $predictor;
    public $setupQuestion;
    public $studyImageFileName;
    public $suffix;
    public $svgPath;
    public $svgUrl;
    public $synonyms;
    public $valence;
    public $variableCategoryNameSingular;
    public $wpPostId;
    public const FIELD_ID = 'id';
    public const FIELD_OUTCOME = 'outcome';
    public const FONT_AWESOME = FontAwesome::TAG_SOLID;
    public const LARAVEL_CLASS = VariableCategory::class;
    public const PROPERTY_CAUSE_ONLY = 'cause_only';
    public const PROPERTY_COMBINATION_OPERATION = 'combination_operation';
    public const FIELD_SYNONYMS = 'synonyms';
    public const PROPERTY_DURATION_OF_ACTION = 'duration_of_action';
    public const PROPERTY_EFFECT_ONLY = 'effect-only';
    public const PROPERTY_FILLING_TYPE = 'filling_type';
    public const PROPERTY_FILLING_VALUE = 'filling_value';
    public const PROPERTY_IMAGE_URL = 'imageUrl';
    public const PROPERTY_MAXIMUM_ALLOWED_VALUE = 'maximum_allowed_value';
    public const PROPERTY_MINIMUM_ALLOWED_VALUE = 'minimum_allowed_value';
    public const PROPERTY_NAME = 'name';
    public const PROPERTY_ONSET_DELAY = 'onset_delay';
    public const PROPERTY_OUTCOME = Variable::FIELD_OUTCOME;
    public const TABLE = 'variable_categories';
    /**
     * VariableCategory constructor.
     * @param QMVariableCategory|object|null $variableCategory
     */
    public function __construct($variableCategory = null){
        if($variableCategory){ // Not populating from Mongo object
            foreach($variableCategory as $key => $value){
                $this->$key = $value;
            }
            //$this->setSynonyms();  These should be hard-coded now because Singularize is slow
        }
    }
    /**
     * @param QMMeasurement|QMVariableRelationship|QMUserVariable|QMCommonVariable|QMTrackingReminderNotification|QMTrackingReminder $item
     * @param bool $unsetUserVariableVariableCategoryId
     * @return \App\VariableRelationships\QMVariableRelationship|\App\Slim\Model\Measurement\QMMeasurement|QMCommonVariable|QMTrackingReminder|QMTrackingReminderNotification|QMUserVariable
     */
    public static function addVariableCategoryNamesToObject($item, bool $unsetUserVariableVariableCategoryId = false){
        if($unsetUserVariableVariableCategoryId && isset($item->userVariableVariableCategoryId)){
            $item->variableCategoryId = $item->userVariableVariableCategoryId;
            unset($item->userVariableVariableCategoryId);
        }
        /** @noinspection MissingIssetImplementationInspection */
        if(isset($item->variableCategoryId)){
            $variableCategoryObject = self::find($item->variableCategoryId);
            $item->variableCategoryName = $variableCategoryObject->name;
        }
        if(isset($item->causeVariableCategoryId) && !isset($item->causeVariableCategoryName)){
            $item->causeVariableCategoryName = self::find($item->causeVariableCategoryId)->name;
        }
        if(isset($item->effectVariableCategoryId) && !isset($item->effectVariableCategoryName)){
            $item->effectVariableCategoryName = self::find($item->effectVariableCategoryId)->name;
        }
        return $item;
    }
    /**
     * @return string
     */
    public function getNameAttribute(): string {
        return $this->name;
    }
    /**
     * @return string
     */
    public function getNamePlural(): string {
        return $this->name;
    }
    /**
     * @return float
     */
    public function getFillingValueAttribute(): ?float {
        return $this->fillingValue;
    }
    /**
     * @return float
     */
    public function getMaximumAllowedValueAttribute(): ?float{
        return $this->maximumAllowedValue;
    }
    /**
     * @return float
     */
    public function getMinimumAllowedValueAttribute(): ?float{
        return $this->minimumAllowedValue;
    }
    /**
     * @return int
     */
    public function getDurationOfAction(): ?int {
        return $this->durationOfAction;
    }
    /**
     * @return boolean
     */
    public function isEffectOnly(): bool{
        return $this->effectOnly;
    }
    /**
     * @return int
     */
    public function getOnsetDelay(): int{
        return $this->onsetDelay;
    }
    /**
     * @return string
     */
    public function getCombinationOperation(): string{
        return $this->combinationOperation;
    }
    /**
     * @return int
     */
    public function getDefaultUnitId(): ?int{
        if(!$this->defaultUnitId){
            QMLog::debug("No default unit for ".$this->name);
        }
        return $this->defaultUnitId;
    }
    /**
     * @return string
     */
    public function getImageUrl(): string {
        return $this->imageUrl;
    }
    /**
     * @return array
     */
    public static function getVariableCategoryNames(): array{
        $variableCategories = self::getVariableCategories();
        foreach($variableCategories as $variableCategory){
            $variableCategoryNames[] = $variableCategory->name;
        }
        if(!isset($variableCategoryNames)){
            throw new LogicException('Could not get variable category names');
        }
        return $variableCategoryNames;
    }
    /**
     * @param string $name
     * @param bool $throwException
     * @return QMVariableCategory
     * @throws VariableCategoryNotFoundException
     */
    public static function findByNameOrSynonym(string $name, bool $throwException = true): ?QMVariableCategory{
        if($name === 'Null'){
            return null;
        }
        if($name === 'Anything'){
            return null;
        }
        $categories = self::getVariableCategories();
        foreach($categories as $category){
            if($category->inSynonyms($name)){
                return $category;
            }
        }
        $allNames = self::getVariableCategoryNames();
        $errorMessage = "Variable category \"$name\" doesn't exist. Current variable categories are: ".
            implode(', ', $allNames).'. Please send an email to '.'help@curedao.org if you want to add a new variable category.';
        if($throwException){
            throw new VariableCategoryNotFoundException($errorMessage);
        }
        QMLog::debug("Variable category \"$name\" doesn't exist");
        return null;
    }
    /**
     * @return QMVariableCategory[]
     */
    public static function getVariableCategories(): array {
        if($all = Memory::getByPrimaryKey(static::TABLE)){
            return $all;
        }  // Getting the file every time is super slow and demolishes CPU!
        $path = self::getHardCodedDirectory();
        $variableCategories = ObjectHelper::instantiateAllModelsInFolder('VariableCategory', $path);
        self::addDefaultUnitIds($variableCategories);
        $variableCategories = self::putCommonAndManualTrackingFirst($variableCategories);
        Memory::setByPrimaryKey(static::TABLE, $variableCategories);
        return $variableCategories;
    }
    /**
     * @param QMVariableCategory[] $variableCategories
     * @return QMVariableCategory[]
     */
    private static function putCommonAndManualTrackingFirst(array $variableCategories): array{
        QMArr::sortByTwoProperties($variableCategories, [
            "common",
            "manualTracking"
        ]);
        return $variableCategories;
    }
    /**
     * @param QMVariableCategory[] $variableCategories
     */
    private static function addDefaultUnitIds(array $variableCategories){
        foreach($variableCategories as $variableCategory){
            if(!empty($variableCategory->defaultUnitAbbreviatedName)){
                $variableCategory->defaultUnitId = QMUnit::getUnitByAbbreviatedName($variableCategory->defaultUnitAbbreviatedName)->id;
            }else{
                $variableCategory->defaultUnitId = null;
            }
        }
    }
    /**
     * @param string $variableCategoryName
     * @return string
     */
    public static function getImagePath(string $variableCategoryName): string{
        return "img/variable_categories/".strtolower(str_replace(' ', '-', $variableCategoryName));
    }
    /**
     * @param QMVariableCategory $category
     * @return QMVariableCategory
     */
    public static function addImageLinks(QMVariableCategory $category): QMVariableCategory{
        $category->svgUrl = IonicHelper::WEB_QUANTIMO_DO.self::getImagePath($category->name).".svg";
        $category->pngUrl = IonicHelper::WEB_QUANTIMO_DO.self::getImagePath($category->name).".png";
        $category->pngPath = self::getImagePath($category->name).".png";
        $category->svgPath = self::getImagePath($category->name).".svg";
        return $category;
    }
    /**
     * @return QMVariableCategory[]
     */
    public static function getVariableCategoriesIndexedById(): array{
        if(self::$variableCategoriesIndexedById){
            return self::$variableCategoriesIndexedById;
        }
        $variableCategoryArray = self::getVariableCategories();
        $variableCategoriesIndexedById = [];
        foreach($variableCategoryArray as $variableCategoryItem){
            $variableCategoriesIndexedById[$variableCategoryItem->id] = $variableCategoryItem;
        }
        self::$variableCategoriesIndexedById = $variableCategoriesIndexedById;
        SwaggerDefinition::addOrUpdateSwaggerDefinition($variableCategoriesIndexedById, __CLASS__);
        return $variableCategoriesIndexedById;
    }
    /**
     * @return QMVariableCategory[]
     */
    public static function getVariableCategoriesIndexedByName(): array{
        $categories = self::getVariableCategories();
        $byName = [];
        foreach($categories as $c){
            $byName[$c->name] = $c;
        }
        return $byName;
    }
    /**
     * @return array
     */
    private static function getSynonymsMap(): array{
        if(self::$synonymsMap){
            return self::$synonymsMap;
        }
        $categories = self::getVariableCategories();
        foreach($categories as $category){
            if($category->synonyms){
                foreach($category->synonyms as $synonym){
                    self::$synonymsMap[$synonym] = $category->name;
                }
            }
        }
        return self::$synonymsMap;
    }
    /**
     * @param string $variableCategoryName
     * @return string
     */
    private static function replaceSynonyms(string $variableCategoryName): string{
        $variableCategoryName = ucwords(trim($variableCategoryName));
        $lowerCase = strtolower($variableCategoryName);
        if(strpos($lowerCase, 'grocer') !== false || strpos($lowerCase, 'food') !== false){
            return FoodsVariableCategory::NAME;
        }
        if(strpos($lowerCase, 'health ') !== false || strpos($lowerCase, 'medication') !== false){
            return TreatmentsVariableCategory::NAME;
        }
        // Legacy => Current
        $synonymsMap = self::getSynonymsMap();
        foreach($synonymsMap as $synonym => $actualName){
            if(strtolower($variableCategoryName) === strtolower($synonym)){
                QMLog::debug('Submitted measurement in '.$synonym.' category. Tell them to switch to '.$actualName);
                return $actualName;
            }
        }
        return $variableCategoryName;
    }
	/**
	 * @param array $array
	 * @param bool $throwException
	 * @return array
	 */
    public static function replaceVariableCategoryNameWithIdInArray(array $array, bool $throwException = true): array{
        if(isset($array['variableCategoryName']) && $array['variableCategoryName'] !== 'Anything'){
            $variableCategory = self::findByNameOrSynonym($array['variableCategoryName'], $throwException);
            if(!$variableCategory){
                return $array;
            }
            $array['variableCategoryId'] = $variableCategory->id;
        }
        unset($array['variableCategoryName']);
        return $array;
    }
    /**
     * @return string
     */
    public static function getStringListOfVariableCategoryNames(): string{
        $variableCategories = self::getVariableCategories();
        $string = '';
        foreach($variableCategories as $variableCategory){
            $string .= $variableCategory->name.', ';
        }
        return $string;
    }
    /**
     * @return string
     */
    public function getIonIcon(): string {
        return $this->ionIcon;
    }
    /**
     * @return string
     */
    public function getSvgUrl(): string {
        return $this->svgUrl;
    }
    /**
     * @return string
     */
    public function getPngUrl(): string {
        return $this->pngUrl;
    }
    /**
     * @param string $originalString
     * @param string $default
     * @return string
     */
    public static function getMostSimilarCategoryName(string $originalString, $default = MiscellaneousVariableCategory::NAME): string{
        $string = self::replaceSynonyms($originalString);
        $string = strtolower($string);
        foreach(self::getVariableCategoryNames() as $variableCategoryName){
            if($string === strtolower($variableCategoryName)){
                return $variableCategoryName;
            }
        }
        foreach(self::getVariableCategoryNames() as $variableCategoryName){
            if(stripos($string, $variableCategoryName) !== false){
                return $variableCategoryName;
            }
            if(stripos($variableCategoryName, $string) !== false){
                return $variableCategoryName;
            }
        }
        $firstWord = QMStr::getFirstWordOfString($string);
        foreach(self::getVariableCategoryNames() as $variableCategoryName){
            if(stripos($variableCategoryName, $firstWord) !== false){
                return $variableCategoryName;
            }
        }
        return $default;
    }
    /**
     * @return bool
     */
    public function isStupidCategory(): bool{
        return in_array($this->getNameAttribute(), self::getStupidCategoryNames(), true);
    }
    /**
     * @return bool
     */
    public function isTreatments(): bool{
        return $this->name === TreatmentsVariableCategory::NAME;
    }
    /**
     * @return bool
     */
    public function isFoods(): bool{
        return $this->name === FoodsVariableCategory::NAME;
    }
    /**
     * @return bool
     */
    public function isSoftware(): bool{
        return $this->name === SoftwareVariableCategory::NAME;
    }
    /**
     * @return bool
     */
    public function isLocation(): bool{
        return $this->name === SoftwareVariableCategory::NAME;
    }
    /**
     * @return bool
     */
    public function isPayments(): bool{
        return $this->name === PaymentsVariableCategory::NAME;
    }
    /**
     * @return bool
     */
    public function isSymptoms(): bool{
        return $this->name === SymptomsVariableCategory::NAME;
    }
    /**
     * @return bool
     */
    public function isNutrients(): bool{
        return $this->name === NutrientsVariableCategory::NAME;
    }
    /**
     * @return bool
     */
    public function isEmotions(): bool{
        return $this->name === EmotionsVariableCategory::NAME;
    }
    /**
     * @return QMVariableCategory
     */
    public static function getFoods(): ?QMVariableCategory{
        return self::findByNameOrSynonym(FoodsVariableCategory::NAME);
    }
    /**
     * @return QMVariableCategory
     */
    public static function getTreatments(): ?QMVariableCategory{
        return self::findByNameOrSynonym(TreatmentsVariableCategory::NAME);
    }
    /**
     * @return QMVariableCategory
     */
    public static function getSymptoms(): ?QMVariableCategory{
        return self::findByNameOrSynonym(SymptomsVariableCategory::NAME);
    }
    /**
     * @return QMVariableCategory
     */
    public static function getSleep(): ?QMVariableCategory{
        return self::findByNameOrSynonym(SleepVariableCategory::NAME);
    }
    /**
     * @return QMVariableCategory
     */
    public static function getSoftware(): ?QMVariableCategory{
        return self::findByNameOrSynonym(SoftwareVariableCategory::NAME);
    }
    /**
     * @return QMVariableCategory
     */
    public static function getPhysicalActivity(): ?QMVariableCategory{
        return self::findByNameOrSynonym(PhysicalActivityVariableCategory::NAME);
    }
    /**
     * @return QMVariableCategory
     */
    public static function getActivities(): ?QMVariableCategory{
        return self::findByNameOrSynonym(ActivitiesVariableCategory::NAME);
    }
    /**
     * @return QMVariableCategory
     */
    public static function getGoals(): ?QMVariableCategory{
        return self::findByNameOrSynonym(GoalsVariableCategory::NAME);
    }
    /**
     * @return QMVariableCategory
     */
    public static function getMisc(): ?QMVariableCategory{
        return self::findByNameOrSynonym(MiscellaneousVariableCategory::NAME);
    }
    /**
     * @return QMVariableCategory
     */
    public static function getEmotions(): ?QMVariableCategory{
        return self::findByNameOrSynonym(EmotionsVariableCategory::NAME);
    }
    /**
     * @return QMVariableCategory
     */
    public static function getPayments(): ?QMVariableCategory{
        return self::findByNameOrSynonym(PaymentsVariableCategory::NAME);
    }
    /**
     * @return QMVariableCategory
     */
    public static function getLocation(): ?QMVariableCategory{
        return self::findByNameOrSynonym(LocationsVariableCategory::NAME);
    }
    /**
     * @return string
     */
    public function getAmazonProductCategory(): ?string {
        return $this->amazonProductCategory;
    }
    /**
     * @param array $params
     * @return self[]
     */
    public static function get(array $params = []): array {
        $arr = self::getVariableCategories();
        return QMArr::filter($arr, $params);
    }
    /**
     * @return array
     */
    protected static function getNotNullColumns(): array {
        return ['effect-only'];
    }
    /**
     * @return string
     */
    public function getSetupQuestion(): string {
        return $this->setupQuestion;
    }
    /** @noinspection PhpUnused */
    public static function updateEntities(): \stdClass{
        $variableCategories = self::getVariableCategories();
        $body = ['name' => 'variable-category-name'];
        foreach($variableCategories as $variableCategory){
            $synonyms = $variableCategory->getOrGenerateSynonyms();
            $body['entries'][] = [
                'value'    => $variableCategory->name,
                'synonyms' => $synonyms
            ];
        }
        FileHelper::writeJsonFile('/vagrant/log', $body, 'variable-category-name-entities');
        $client = new QMEntityTypesClient();
        $client->updateEntities('variableCategoryName', $body['entries']);
        $response = APIHelper::makePutRequest('https://api.dialogflow.com/v1/entities?v=20150910', $body,
	        '9a5901a820574462a4379060e961625f');
        return $response;
    }
    /**
     * @return array
     */
    public function getOrGenerateSynonyms(): array {
        if(is_object($this->synonyms)){
            $this->synonyms = json_decode(json_encode($this->synonyms), true);
        }
        if($this->synonyms === null){
            return $this->generateSynonyms();
        }
        return $this->synonyms;
    }
    /**
     * @return array
     */
    public function generateSynonyms(): array {
        if(is_object($this->synonyms)){
            $this->synonyms = json_decode(json_encode($this->synonyms), true);
        }
        $synonyms = $this->synonyms;
        if(!$synonyms){
            $synonyms = [];
        }
        $synonyms[] = $this->name;
        $synonyms[] = $this->getNameSingular();
        $synonyms[] = $this->amazonProductCategory;
        $synonyms = QMStr::addSingularVersions($synonyms);
        return $this->synonyms = $synonyms;
    }
    /**
     * @return string
     */
    public function getSuffix(): ?string {
        return $this->suffix;
    }
    /**
     * @return int
     */
    public function getMinimumAllowedSecondsBetweenMeasurements(): ?int {
        return $this->minimumAllowedSecondsBetweenMeasurements;
    }
    /**
     * @return string
     */
    public function getStudyImageFileName(): string {
        if(!$this->studyImageFileName){
            $this->studyImageFileName = str_replace(' ', '-', strtolower($this->getNameAttribute()));
        }
        return $this->studyImageFileName;
    }
    /**
     * @return string
     */
    public function __toString(){
        return $this->getNameAttribute()." ($this->id)";
    }
    /**
     * @return mixed
     */
    public function getValence(): string {
        if (!$this->valence) {
            $this->valence = BaseValenceProperty::VALENCE_NEUTRAL;
        }
        return $this->valence;
    }
    public function getTitleAttribute():string{
        return $this->getNameAttribute();
    }
    public function getFontAwesome(): string {
        return $this->fontAwesome;
    }
    public function getSubtitleAttribute(): string{
        if(!$this->moreInfo){
            le("Please set moreInfo for ".static::class);
        }
        return $this->moreInfo;
    }
    /**
     * @return QMVariableCategory[]
     */
    public static function getAll(): array{
        return self::getVariableCategories();
    }
    /**
     * @return VariableCategory
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection PhpReturnDocTypeMismatchInspection
     */
    public function l(){
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->attachedOrNewLaravelModel();
    }
    /**
     * @return string
     */
    public function getFillingType(): ?string {
        return $this->fillingType;
    }
    /**
     * @return bool
     */
    public function getManualTracking(): ?bool {
        return $this->manualTracking;
    }
    /**
     * @return string
     */
    public static function getHardCodedDirectory(): string{
        return FileHelper::absPath("app/VariableCategories");
    }
    /**
     * @param string|int $nameOrId
     * @return QMVariableCategory
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public static function find($nameOrId): ?DBModel{
        if($nameOrId instanceof self){
            return $nameOrId;
        }
        if(is_int($nameOrId)){
            if(!$nameOrId){le("no cat id");}
            $arr = self::getVariableCategoriesIndexedById();
            return $arr[$nameOrId];
        }
        $cat = self::findByNameOrSynonym($nameOrId);
		if(!$cat){le("Category $nameOrId not found!");}
        return $cat;
    }
    /**
     * @return Variable|\Illuminate\Database\Query\Builder
     */
    public static function variables() {
        return Variable::whereVariableCategoryId(static::ID);
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public static function getVariableNames(): Collection {
        return static::variables()->pluck(Variable::FIELD_NAME);
    }
    public function updateVariableAttributesIfNotNull(string $key){
        $val = $this->getAttribute($key);
        if($val !== null){
            $qb = $this->variablesQB();
            try {
                Variable::updateAttributeWhereNecessary($qb, $key, $val);
            } catch (\Throwable $e){
                Variable::updateAttributeWhereNecessary($qb, $key, $val);
            }
        }
    }
    /**
     * @return Variable|\Illuminate\Database\Query\Builder
     */
    public function variablesQB(){
        return Variable::whereVariableCategoryId($this->id);
    }
    public function populateByLaravelModel(BaseModel $l){
        $this->logInfo("We shouldn't populate variable categories from hard coded models");
        //parent::populateByLaravelModel($l);
    }
    public static function getAllFromMemoryIndexedByUuidAndId(): array{
        $mem = parent::getAllFromMemoryIndexedByUuidAndId();
        if(!$mem){
            $all = static::all();
            foreach($all as $one){
				$one->addToMemory();
            }
        }
        return parent::getAllFromMemoryIndexedByUuidAndId();
    }
    protected static function getMemoryPrimaryKey(): string{return 'QMVariableCategory';}
    /**
     * @return Builder|HasMany
     * @noinspection PhpReturnDocTypeMismatchInspection
     */
    public function indexVariablesQB(){
        return $this->publicStudyVariablesQB();
    }
	/**
	 * @return VariableButton[]
	 */
	public function getVariableButtons(): array{
		return VariableButton::getWithStudies([static::ID]);
	}
	/**
	 * @return Collection|Variable[]
	 */
	public function getIndexVariables(): Collection{
		$qb = $this->indexVariablesQB();
		$variables = $qb->get();
		return $variables;
	}
	public function getHardCodedClassFile(): PhpClassFile{
		return new VariableCategoryFile($this->l());
	}
	protected function generateFileContentOfHardCodedModel(): string{
		$file = $this->getHardCodedClassFile();
		$file->updateFromDB();
		return $file->getContents();
	}
	protected function getHardCodedShortClassName(): string{
		return QMStr::toShortClassName($this->getNameAttribute()) . "VariableCategory";
	}
	/**
	 * @param int|string $nameOrId
	 * @return static|null
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function findByNameIdOrSynonym($nameOrId){
		return static::find($nameOrId);
	}
	public function getUrl(array $params = []): string{
		return qm_url($this->getShowFolderPath());
	}
	public function getVariableCategory(): VariableCategory{
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $this->attachedOrNewLaravelModel();
	}
	/**
	 * @return Collection|Variable[]
	 */
	public function getVariablesOrButtons(){
		return $this->getIndexVariables();
	}
	public function getShowContentView(array $params = []): View{
		return view('variable-category-content', $this->getShowParams($params));
	}
	public function getNotFoundButtons():array{
		return [
			OnboardingStateButton::instance(),
			Variable::getSearchAllIndexButton(),
		];
	}
	public function getIcon(): string{
		return $this->imageUrl;
	}
	public function getShowPageView(array $params = []): View{
		$params['model'] = $params['category'] = $this;
		return view('variable-category', $params);
	}
	public function getPlaceholder():string{
		$name = $this->getNameSingular();
		if(empty($name)){le("no name!");}
		return "Search for a ".strtolower($name)."...";
	}
	public function publicStudyVariablesQB(): Builder {
		$qb = Variable::indexQBWithCorrelations();
		$qb->where(Variable::FIELD_IS_PUBLIC, true);
		$qb->orderByDesc(Variable::FIELD_NUMBER_OF_USER_VARIABLES);
		$qb->where(Variable::FIELD_NUMBER_OF_USER_VARIABLES, ">", 1);
		return $qb->where(Variable::FIELD_VARIABLE_CATEGORY_ID, $this->getId());
	}
	public function getQMVariableCategory():QMVariableCategory{
		return $this->getDBModel();
	}
	/**
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function getVariablesIndex(){
		return view('variable-category', [
			'c' => $this,
		]);
	}
	public function getVariableChipSearch(): string{
		if($this->getNumberOfOutcomePopulationStudies() > 20 || $this->getNumberOfPredictorPopulationStudies()){
			return VariableButton::chipSearchForCategoryWithStudies($this->getId());
		}
		return VariableButton::chipSearchForCategory($this->getId());
	}
	public static function getIndexPageView(): View{
		$name = static::FIELD_NAME;
		return view('variable-categories-index', [
			'heading' => $name,
			'buttons' => static::getIndexButtons()
		]);
	}
}
