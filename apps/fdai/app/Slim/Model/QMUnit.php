<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model;
use App\CodeGenerators\Swagger\SwaggerDefinition;
use App\Exceptions\BadRequestException;
use App\Exceptions\CommonVariableNotFoundException;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidVariableNameException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\NotFoundException;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Models\Unit;
use App\Models\UserVariable;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Properties\Base\BaseValenceProperty;
use App\Properties\Unit\UnitNameProperty;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\Measurement\ValueConverter;
use App\Slim\Model\Reminders\QMTrackingReminder;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Traits\ModelTraits\UnitTrait;
use App\Traits\QMAnalyzableTrait;
use App\Types\ObjectHelper;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UI\ImageUrls;
use App\UnitCategories\CountUnitCategory;
use App\UnitCategories\CurrencyUnitCategory;
use App\UnitCategories\DurationUnitCategory;
use App\UnitCategories\MiscellanyUnitCategory;
use App\UnitCategories\RatingUnitCategory;
use App\UnitCategories\WeightUnitCategory;
use App\Units\CountUnit;
use App\Units\DollarsUnit;
use App\Units\HoursUnit;
use App\Units\InternationalUnitsUnit;
use App\Units\MilligramsUnit;
use App\Units\MinutesUnit;
use App\Units\OneToFiveRatingUnit;
use App\Units\OneToTenRatingUnit;
use App\Units\PercentUnit;
use App\Units\SecondsUnit;
use App\Units\ServingUnit;
use App\Units\YesNoUnit;
use App\Utils\APIHelper;
use App\Utils\Stats;
use App\VariableCategories\TreatmentsVariableCategory;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserTag;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
use Illuminate\Support\Arr;
use stdClass;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
/**
 * @mixin Unit
 */
class QMUnit extends DBModel {
	use UnitTrait;
	public const LARAVEL_CLASS = Unit::class;
	private static $nonAdvanced;
	private static $units;
	private static $unitsIndexedById;
	private static $unitsWithAbbreviatedNameAsKey;
	private static $unitsWithLowerCaseAbbreviatedNameAsKey;
	private static $unitsWithLowerCaseNameAsKey;
	private static $unitsWithLowerCaseSynonymAsKey = [];
	private static $valueAndUnitStrings;
	public $abbreviatedName;
	public $add;
	public $advanced;
	public $categoryName;
	public $combinationOperation;
	public $conversionSteps;
	public $defaultValue;
	public $fillingType = BaseFillingTypeProperty::FILLING_TYPE_NONE;
	public ?float $fillingValue;
	public $fontAwesome;
	public $hint;
	public $image = ImageUrls::FITNESS_MEASURING_TAPE;
	public $inputType;
	public $manualTracking;
	public $maximumDailyValue;
	public $maximumValue;
	public $minimumValue;
	public $multiply;
	public $name;
	public $numberOfCommonTagsWhereTaggedVariableUnit;
	public $numberOfCommonTagsWhereTagVariableUnit;
	public $numberOfMeasurements;
	public $numberOfOutcomeCaseStudies;
	public $numberOfOutcomePopulationStudies;
	public $numberOfUserVariablesWhereDefaultUnit;
	public $numberOfVariableCategoriesWhereDefaultUnit;
	public $numberOfVariablesWhereDefaultUnit;
	public $suffix;
	public $synonyms;
	public $unitCategoryId;
	public const FIELD_CATEGORY_ID = 'category_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_ID = 'id';
	public const FIELD_MAXIMUM_VALUE = 'maximum_value';
	public const FIELD_MINIMUM_VALUE = 'minimum_value';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const INPUT_TYPE_bloodPressure = 'bloodPressure';
	public const INPUT_TYPE_happiestFaceIsFive = 'happiestFaceIsFive';
	public const INPUT_TYPE_oneToFiveNumbers = 'oneToFiveNumbers';
	public const INPUT_TYPE_oneToTen = 'oneToTen';
	public const INPUT_TYPE_saddestFaceIsFive = 'saddestFaceIsFive';
	public const INPUT_TYPE_slider = 'slider';
	public const INPUT_TYPE_value = 'value';
	public const INPUT_TYPE_yesOrNo = 'yesOrNo';
	public const TABLE = Unit::TABLE;
	public $scale;
	// Ordinal is used to simply depict the order of variables and not the difference between each of the variables. These scales
	// are generally used to depict non-mathematical ideas such as frequency, satisfaction, happiness, a degree of pain etc.
	public const SCALE_ORDINAL = 'ordinal';
	// Ratio Scale not only produces the order of variables but also
	// makes the difference between variables known along with information on the value of true zero.
	public const SCALE_RATIO = 'ratio';
	// Interval scale contains all the properties of ordinal scale, in addition to which, it offers a calculation of the
	// difference between variables. The main characteristic of this scale is the equidistant difference between objects.
	// Interval has no pre-decided starting point or a true zero value.
	public const SCALE_INTERVAL = 'interval';
	// Nominal, also called the categorical variable scale, is defined as a scale used for labeling variables into
	// distinct classifications and doesn’t involve a quantitative value or order.
	public const SCALE_NOMINAL = 'nominal';
	/**
	 * Unit constructor.
	 * @param $unit
	 */
	public function __construct($unit = null){
		if(!$unit){
			return;
		}
		foreach($unit as $key => $value){
			$this->$key = $value;
		}
		//$this->setSynonyms();  These should be hard-coded now because Singularize is slow
	}
	/**
	 * @return mixed|stdClass
	 */
	public static function updateUnitNameEntities(): stdClass{
		$units = self::getUnits();
		$body = ['name' => 'unit-name'];
		foreach($units as $unit){
			$synonyms = $unit->getOrGenerateSynonyms();
			$body['entries'][] = [
				'value' => $unit->name,
				'synonyms' => $synonyms,
			];
		}
		FileHelper::writeJsonFile('/vagrant/log', $body, 'unit-name-entities');
		$body = ['name' => 'unit-abbreviated-name'];
		foreach($units as $unit){
			$synonyms = $unit->getOrGenerateSynonyms();
			$body['entries'][] = [
				'value' => $unit->abbreviatedName,
				'synonyms' => $synonyms,
			];
		}
		FileHelper::writeJsonFile('/vagrant/log', $body, 'unit-abbreviated-name-entities');
		$response = APIHelper::makePostRequest('https://api.dialogflow.com/v1/entities?v=20150910', $body,
			'9a5901a820574462a4379060e961625f');
		return $response;
	}
	/**
	 * @return QMUnit
	 */
	public static function getPercent(): QMUnit{
		return self::getByNameOrId(PercentUnit::NAME);
	}
	public static function idExists(int $id): bool{
		return isset(self::getUnitsIndexedById()[$id]);
	}
	/**
	 * @return string[]
	 */
	public static function getAbbreviatedNames(): array{
		return QMArr::pluckColumn(static::all(), 'abbreviatedName');
	}
	/**
	 * @param $originalValue
	 * @param QMUnit $fromUnit
	 * @return int
	 */
	public static function convertToYesNoFromRating($originalValue, QMUnit $fromUnit): int{
		if($originalValue > $fromUnit->averageAllowedValue()){
			return 1;
		}
		return 0;
	}
	/**
	 * @return QMUnit[]
	 */
	public static function getCountCategoryUnits(): array{
		return Arr::where(self::getUnits(), static function($unit){
			/** @var QMUnit $unit */
			return $unit->isCountCategory();
		});
	}
	/**
	 * @return QMUnit
	 */
	public static function getMilligrams(): QMUnit{
		return self::getUnitByFullName(MilligramsUnit::NAME);
	}
	/**
	 * @return bool
	 */
	public function isRating(): bool{
		return $this->categoryName === RatingUnitCategory::NAME;
	}
	/**
	 * @return float
	 */
	public function averageAllowedValue(){
		return ($this->maximumValue - $this->minimumValue) / 2;
	}
	/**
	 * @return QMUnit[]
	 */
	public static function getUnits(): array{
		if(self::$units){
			return self::$units;
		}
		/** @var QMUnit[] $units */
		$units = ObjectHelper::instantiateAllModelsInFolder('Unit', self::getHardCodedDirectory());
		foreach($units as $unit){
			$conversionStepArrays = $unit->conversionSteps;
			$unit->conversionSteps = [];
			foreach($conversionStepArrays as $conversionStepArray){
				$unit->conversionSteps[] = ObjectHelper::convertToObject($conversionStepArray);
			}
		}
		SwaggerDefinition::addOrUpdateSwaggerDefinition($units, __CLASS__);
		return self::$units = $units;
	}
	/**
	 * @return QMUnit[]
	 */
	public static function getNonAdvancedUnits(): array{
		if(self::$nonAdvanced){
			return self::$nonAdvanced;
		}
		$units = self::getUnits();
		$nonAdvanced = [];
		foreach($units as $unit){
			if(!$unit->advanced){
				$nonAdvanced[] = $unit;
			}
		}
		return self::$nonAdvanced = $nonAdvanced;
	}
	/**
	 * @return QMUnit[]
	 */
	public static function getUnitsIndexedById(): array{
		if(isset(self::$unitsIndexedById)){
			return self::$unitsIndexedById;
		}
		$units = self::getUnits();
		$unitsIndexedById = [];
		foreach($units as $unit){
			$unitsIndexedById[$unit->id] = $unit;
		}
		return self::$unitsIndexedById = $unitsIndexedById;
	}
	/**
	 * @param QMUserVariable|QMCommonVariable|QMTrackingReminderNotification|QMTrackingReminder|QMMeasurement $item
	 */
	public static function setInputType($item){
		$item->inputType = self::INPUT_TYPE_value;
		if(isset($item->unitId)){
			$unit = self::getUnitById($item->unitId); // Much more efficient
		} else{
			$unit = self::findByNameOrSynonym($item->unitAbbreviatedName);
		}
		$abbreviated = $unit->abbreviatedName;
		// Slider is stupid if(isset($item->maximumAllowedValue, $item->minimumAllowedValue)){$item->inputType = self::INPUT_TYPE_slider;}
		// Slider is stupid if(isset($unit->maximumValue, $unit->minimumValue)){$item->inputType = self::INPUT_TYPE_slider;}
		if(isset($item->variableName) && $item->variableName === 'Blood Pressure'){
			$item->inputType = self::INPUT_TYPE_bloodPressure;
		}
		if($abbreviated === OneToFiveRatingUnit::ABBREVIATED_NAME){
			$item->inputType = self::INPUT_TYPE_oneToFiveNumbers;
			$polarity = ($item->valence ?? null);
			if($polarity === BaseValenceProperty::VALENCE_POSITIVE){
				$item->inputType = self::INPUT_TYPE_happiestFaceIsFive;
			}
			if($polarity === BaseValenceProperty::VALENCE_NEGATIVE){
				$item->inputType = self::INPUT_TYPE_saddestFaceIsFive;
			}
		}
		if($abbreviated === YesNoUnit::ABBREVIATED_NAME){
			$item->inputType = self::INPUT_TYPE_yesOrNo;
		}
		if($abbreviated === OneToTenRatingUnit::ABBREVIATED_NAME){
			$item->inputType = self::INPUT_TYPE_oneToTen;
		}
	}
	/**
	 * @param int $id
	 * @return QMUnit
	 */
	public static function getUnitById(int $id, bool $throwException = true): ?QMUnit{
		if(!$id){
			le("Please provide unit id");
		}
		$unitsIndexedById = self::getUnitsIndexedById();
		if(!isset($unitsIndexedById[$id])){
            if(!$throwException){
                return null;
            }
			throw new BadRequestException("Unit id: $id not found! ");
		}
		return $unitsIndexedById[$id];
	}
	public static function findByNameOrAbbreviatedName(string $nameOrAbbreviated): ?QMUnit{
		if($u = self::getUnitByFullName($nameOrAbbreviated)){
			return $u;
		}
		return self::getUnitByAbbreviatedName($nameOrAbbreviated);
	}
	/**
	 * @param string $unitName
	 * @param bool $throwExceptionIfNotFound
	 * @return QMUnit
	 */
	public static function findByNameOrSynonym(string $unitName, bool $throwExceptionIfNotFound = true): ?QMUnit{
		if($unit = self::findByNameOrAbbreviatedName($unitName)){
			return $unit;
		}
		$unit = self::getUnitBySynonyms($unitName);
		if($unit){
			return $unit;
		}
		if($throwExceptionIfNotFound){
			throw new BadRequestException('Could not find unit named ' . $unitName . '.  Available units are ' .
				UnitNameProperty::getList());
		}
		return null;
	}
	/**
	 * @param string $string
	 * @param bool $caseSensitive
	 * @param bool $includeAdvanced
	 * @return QMUnit
	 */
	public static function getUnitByAbbreviatedName(string $string, bool $caseSensitive = false,
		bool $includeAdvanced = true): ?QMUnit{
		if($caseSensitive){
			$units = self::getAllWithAbbreviatedNameAsKey();
		} else{
			$string = strtolower($string);
			$units = self::getAllWithLowerCaseAbbreviatedNameAsKey();
		}
		if(isset($units[$string]) && ($includeAdvanced || !$units[$string]->advanced)){
			return $units[$string];
		}
		return null;
	}
	/**
	 * @param string $string
	 * @param bool $includeAdvanced
	 * @return QMUnit
	 */
	public static function getUnitBySynonyms(string $string, bool $includeAdvanced = true): ?QMUnit{
		$string = strtolower($string);
		$units = self::getAllWithLowerCaseSynonymAsKey();
		if(isset($units[$string])){
			if($includeAdvanced || !$units[$string]->advanced){
				return $units[$string];
			}
		}
		return null;
	}
	/**
	 * @param string $unitName
	 * @return QMUnit
	 */
	public static function getUnitByFullName(string $unitName): ?QMUnit{
		$unitName = strtolower($unitName);
		$units = self::getAllWithLowerCaseNameAsKey();
		return $units[$unitName] ?? null;
	}
	/**
	 * @return QMUnit[]
	 */
	public static function getAllWithAbbreviatedNameAsKey(): array{
		if(!empty(self::$unitsWithAbbreviatedNameAsKey)){
			return self::$unitsWithAbbreviatedNameAsKey;
		}
		$unitsArray = self::getUnits();
		foreach($unitsArray as $unit){
			self::$unitsWithAbbreviatedNameAsKey[$unit->abbreviatedName] = $unit;
		}
		return self::$unitsWithAbbreviatedNameAsKey;
	}
	/**
	 * @return QMUnit[]
	 */
	public static function getAllWithLowerCaseNameAsKey(): array{
		if(!empty(self::$unitsWithLowerCaseNameAsKey)){
			return self::$unitsWithLowerCaseNameAsKey;
		}
		$unitsArray = self::getUnits();
		foreach($unitsArray as $unit){
			self::$unitsWithLowerCaseNameAsKey[strtolower($unit->name)] = $unit;
		}
		return self::$unitsWithLowerCaseNameAsKey;
	}
	/**
	 * @return QMUnit[]
	 */
	private static function getAllWithLowerCaseAbbreviatedNameAsKey(): array{
		if(self::$unitsWithLowerCaseAbbreviatedNameAsKey){
			return self::$unitsWithLowerCaseAbbreviatedNameAsKey;
		}
		$unitsArray = self::getUnits();
		$indexed = [];
		foreach($unitsArray as $unit){
			$indexed[strtolower($unit->abbreviatedName)] = $unit;
		}
		return self::$unitsWithLowerCaseAbbreviatedNameAsKey = $indexed;
	}
	/**
	 * @return QMUnit[]
	 */
	private static function getAllWithLowerCaseSynonymAsKey(): array{
		if(self::$unitsWithLowerCaseSynonymAsKey){
			return self::$unitsWithLowerCaseSynonymAsKey;
		}
		$unitsArray = self::getUnits();
		foreach($unitsArray as $unit){
			if(!empty($unit->synonyms)){
				foreach($unit->synonyms as $synonym){
					self::$unitsWithLowerCaseSynonymAsKey[strtolower($synonym)] = $unit;
				}
			}
		}
		return self::$unitsWithLowerCaseSynonymAsKey;
	}
	/**
	 * @param string $unitCategoryName
	 * @return QMUnit[]
	 */
	public static function getUnitsByUnitCategoryName(string $unitCategoryName): array{
		$units = self::getAllWithAbbreviatedNameAsKey();
		$unitsInCategory = [];
		foreach($units as $unit){
			if($unit->categoryName === $unitCategoryName){
				$unitsInCategory[] = $unit;
			}
		}
		return $unitsInCategory;
	}
	/**
	 * @param string|int $variableNameOrId
	 * @return QMUnit[]
	 * @throws CommonVariableNotFoundException
	 */
	public static function getUnitsForVariableByVariableNameOrId($variableNameOrId): array{
		$variable = QMCommonVariable::findByNameOrId($variableNameOrId);
		if(!$variable){
			throw new CommonVariableNotFoundException("Variable $variableNameOrId not found!");
		}
		return $variable->getUserOrCommonUnit()->getCompatibleUnits();
	}
	/**
	 * @return array|QMUnit[]
	 */
	public function getCompatibleUnits(): array{
		$availableUnits = [];
		if($this->categoryName === 'Miscellany'){
			$availableUnits[] = self::getUnitById($this->id);
		} else{
			$availableUnits = self::getUnitsByUnitCategoryName($this->categoryName);
		}
		if(in_array($this->abbreviatedName, self::getYesNoConvertibleUnitAbbreviatedNames(), true)){
			$availableUnits[] = self::getUnitById(YesNoUnit::ID);
		}
		return $availableUnits;
	}
	public function getCompatibleUnitsList(): string{
		$names = collect($this->getCompatibleUnits())->pluck('name')->all();
		return "\n\t- " . implode("\n\t- ", $names);
	}
	/**
	 * @param int|QMUnit $variableUnitId
	 * @param int|QMUnit $submittedUnitNameOrId
	 * @return bool
	 */
	public static function unitIsIncompatible($variableUnitId, $submittedUnitNameOrId): bool{
		if($variableUnitId === $submittedUnitNameOrId){
			return false;
		}
		if($variableUnitId instanceof static){
			$variableUnit = $variableUnitId;
		} else{
			$variableUnit = self::getUnitById($variableUnitId);
		}
		if($submittedUnitNameOrId instanceof static){
			$submittedUnitObject = $submittedUnitNameOrId;
		} else{
			$submittedUnitObject = self::getByNameOrId($submittedUnitNameOrId);
		}
		$sameUnitCategories = $variableUnit->categoryName === $submittedUnitObject->categoryName;
		$miscellaneousUnitCategoryId = 6;
		$isMiscellanyAndDifferent = ($miscellaneousUnitCategoryId === $submittedUnitObject->categoryName);
		$unitIsIncompatible = !$sameUnitCategories || $isMiscellanyAndDifferent;
		if($unitIsIncompatible){
			$unitIsIncompatible = !self::canWeConvertFromYesNo($submittedUnitObject, $variableUnit);
		}
		return $unitIsIncompatible;
	}
	/**
	 * @param $otherUnitNameOrId
	 * @return bool
	 */
	public function isCompatibleWith($otherUnitNameOrId): bool{
		$incompatible = self::unitIsIncompatible($this->id, $otherUnitNameOrId);
		return !$incompatible;
	}
	/**
	 * @param QMUnit $submittedUnitObject
	 * @param QMUnit $defaultUnit
	 * @return bool
	 * @internal param UserVariable|Variable $variableObject
	 */
	public static function canWeConvertFromYesNo(QMUnit $submittedUnitObject, QMUnit $defaultUnit): bool{
		if($submittedUnitObject->abbreviatedName !== 'yes/no'){
			return false;
		}
		if($defaultUnit->isYesNo()){
			return false;
		}
		if($defaultUnit->isRating()){
			return true;
		}
		if(self::isDefaultUnitYesNoConvertible($defaultUnit)){
			return true;
		}
		return false;
	}
	/**
	 * @param QMUnit $defaultUnitObject
	 * @return bool
	 */
	public static function isDefaultUnitYesNoConvertible(QMUnit $defaultUnitObject): bool{
		return $defaultUnitObject->isYesNoConvertible();
	}
	/**
	 * @return bool
	 */
	private function isYesNoConvertible(): bool{
		return in_array($this->abbreviatedName, self::getYesNoConvertibleUnitAbbreviatedNames(), true);
	}
	/**
	 * @return array
	 */
	public static function getYesNoConvertibleUnitAbbreviatedNames(): array{
		return [
			'applications',
			'capsules',
			'count',
			'event',
			'serving',
			'sprays',
			'tablets',
			'units',
		];
	}
	/**
	 * @return bool
	 */
	public function isYesNo(): bool{
		return $this->name === YesNoUnit::NAME;
	}
	/**
	 * @param float|string|int $value
	 * @return float
	 */
	public static function convertToYesNoFromCountCategory($value){
		$value = (int)$value;
		$value = $value > 0 ? 1 : 0;
		return $value;
	}
	/**
	 * @param QMUserVariable|QMVariable $tagVariable
	 * @param QMUserVariable|QMVariable $taggedVariable
	 * @return bool
	 */
	public static function variablesAreTagCompatible($tagVariable, $taggedVariable): bool{
		if(!$taggedVariable){
			le("No tagged variable!");
		}
		$error = "$tagVariable->name and $taggedVariable->name are not tag compatible! Deleting user tags...";
		$meta = [
			'tag variable' => $tagVariable,
			'tagged variable' => $taggedVariable,
		];
		if($taggedVariable->isRating() && !$tagVariable->isRating()){
			QMLog::error($error, $meta);
			QMUserTag::writable()->where('tag_variable_id', $tagVariable->getVariableIdAttribute())
				->where('tagged_variable_id', $taggedVariable->getVariableIdAttribute())->delete();
			return false;
		}
		if($tagVariable->isRating() && !$taggedVariable->isRating()){
			QMLog::error($error, $meta);
			QMUserTag::writable()->where('tag_variable_id', $tagVariable->getVariableIdAttribute())
				->where('tagged_variable_id', $taggedVariable->getVariableIdAttribute())->delete();
			return false;
		}
		return true;
	}
	/**
	 * @param float $originalValue
	 * @param int $fromUnitId
	 * @param int $toUnitId
	 * @param null|QMAnalyzableTrait|\App\Slim\Model\DBModel $analyzable
	 * @param int|null $durationInSeconds
	 * @return float
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	public static function convertValueByUnitIds(float $originalValue, int $fromUnitId, int $toUnitId, $analyzable = null,
		int $durationInSeconds = null){
		if(!$toUnitId){
			throw new BadRequestHttpException("Please provide toUnitId");
		}
		$fromUnit = self::getUnitById($fromUnitId);
		if(!$fromUnit){
			le("fromUnit id: $fromUnitId not found!");
		}
		$toUnit = self::getUnitById($toUnitId);
		if(!$toUnit){
			le("toUnit id: $toUnitId not found!");
		}
		if($fromUnit->isYesNo() && $toUnit->isRating()){
			return YesNoUnit::toRating($originalValue, $toUnit);
		}
		if($toUnit->isYesNo() && $fromUnit->isRating()){
			return self::convertToYesNoFromRating($originalValue, $fromUnit);
		}
		if($fromUnit->isYesNo() && $toUnit->isCountCategory()){
			return YesNoUnit::toNumber($originalValue);
		}
		if($toUnit->isYesNo() && $fromUnit->isCountCategory()){
			return self::convertToYesNoFromCountCategory($originalValue);
		}
		$convertedValue = self::convertValue($originalValue, $fromUnit, $toUnit, $analyzable, $durationInSeconds);
		if(!isset($convertedValue)){
			le("No converted value!  Trying to convert " . $originalValue .
				" $fromUnit->abbreviatedName to $toUnit->abbreviatedName");
		}
		return $convertedValue;
	}
	/**
	 * @param float $originalValue
	 * @param string|int $fromUnitNameOrId
	 * @param string|int $toUnitNameOrId
	 * @param QMAnalyzableTrait|\App\Slim\Model\DBModel $analyzable
	 * @param int|null $durationInSeconds
	 * @return float
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	public static function convertValueByUnitNameOrId(float $originalValue, $fromUnitNameOrId, $toUnitNameOrId, $analyzable,
		int $durationInSeconds = null): ?float{
		if($fromUnitNameOrId === $toUnitNameOrId){
			return $originalValue;
		}
		if(!isset($originalValue)){
			return null;
		}
		$fromUnit = self::getByNameOrId($fromUnitNameOrId);
		$toUnit = self::getByNameOrId($toUnitNameOrId);
		return self::convertValue($originalValue, $fromUnit, $toUnit, $analyzable, $durationInSeconds);
	}
	/**
	 * @param $item
	 */
	public static function addUnitNames($item){
		$unitsIndexedById = self::getUnitsIndexedById();
		if(isset($item->unitId, $unitsIndexedById[$item->unitId])){
			$u = $unitsIndexedById[$item->unitId];
			if(property_exists($item, 'unitAbbreviatedName')){
				$item->unitAbbreviatedName = $u->abbreviatedName;
			}
			if(property_exists($item, 'unitName')){
				$item->unitName = $u->name;
			}
			if(property_exists($item, 'unitCategoryId')){
				$item->unitCategoryId = $u->unitCategoryId;
			}
			if(property_exists($item, 'unitCategoryName')){
				$item->unitCategoryName = $u->categoryName;
			}
		}
	}
	/**
	 * @param QMUserVariable|QMTrackingReminder|QMTrackingReminderNotification|QMCommonVariable|QMMeasurement $item
	 */
	public static function addUnitProperties($item){
		self::addUnitNames($item);
		if(property_exists($item, 'inputType')){
			self::setInputType($item);
		}
	}
	/**
	 * @param string $string
	 * @return string
	 */
	public static function removeSpaceBeforeSlash(string $string): string{
		return str_replace(' /', '/', $string);
	}
	/**
	 * @param array $array
	 * @return array
	 */
	public static function replaceUnitNamesWithUnitIds(array $array): array{
		if(isset($array['unitAbbreviatedName'])){
			$array['unitId'] = self::findByNameOrSynonym($array['unitAbbreviatedName'])->id;
			unset($array['unitAbbreviatedName'], $array['unitName']);
		}
		if(isset($array['defaultUnitAbbreviatedName'])){
			$array['defaultUnitId'] = self::findByNameOrSynonym($array['defaultUnitAbbreviatedName'])->id;
			unset($array['defaultUnitAbbreviatedName'], $array['defaultUnitName']);
		}
		if(isset($array['userUnitAbbreviatedName'])){
			$array['userUnitId'] = self::findByNameOrSynonym($array['userUnitAbbreviatedName'])->id;
			unset($array['userUnitAbbreviatedName'], $array['userUnitName']);
		}
		if(isset($array['unitName'])){
			$array['unitId'] = self::findByNameOrSynonym($array['unitName'])->id;
			unset($array['unitName']);
		}
		if(isset($array['defaultUnitName'])){
			$array['defaultUnitId'] = self::findByNameOrSynonym($array['defaultUnitName'])->id;
			unset($array['defaultUnitName']);
		}
		if(isset($array['userUnitName'])){
			$array['userUnitId'] = self::findByNameOrSynonym($array['userUnitName'])->id;
			unset($array['userUnitName']);
		}
		return $array;
	}
	/**
	 * @param float|string|int $fromValue
	 * @param QMUnit|int|string $fromUnitNameOrId
	 * @param QMUnit|int|string $toUnitNameOrId
	 * @param QMAnalyzableTrait|BaseModel $analyzable
	 * @param int|null $durationInSeconds
	 * @return float
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	public static function convertValue($fromValue, $fromUnitNameOrId, $toUnitNameOrId, $analyzable,
		int $durationInSeconds = null,bool $validate = true): ?float{
		if($fromValue === null){
			return null;
		}
		if(is_string($fromValue) && stripos($fromValue, 'infinity') !== false){
			return null;
		}
		if(!$toUnitNameOrId){
			return $fromValue;
		}
		if(!$fromUnitNameOrId){
			return $fromValue;
		}
		if($toUnitNameOrId === $fromUnitNameOrId){
			if(!is_numeric($fromValue)){
				le("$fromValue is not numeric! It is: ", $fromValue);
			}
			return $fromValue;
		}
		$mv = new ValueConverter();
		$fromUnitNameOrId = $mv->setFromUnit($fromUnitNameOrId);
		$toUnitNameOrId = $mv->setToUnit($toUnitNameOrId);
		$fromValue = YesNoUnit::toNumber($fromValue);
		if($fromUnitNameOrId->id === $toUnitNameOrId->id){
			return $fromValue;
		}
		$mv->setFromValue($fromValue);
		$converted = $mv->convert($analyzable, $durationInSeconds, $validate);
		return Stats::roundToSignificantFiguresIfGreater($converted, 3);
	}
	/**
	 * @param QMUnit|Unit|int|string $fromUnit
	 * @param QMUnit|Unit|int|string $toUnit
	 * @param QMUserVariable|UserVariable $variable
	 * @throws IncompatibleUnitException
	 */
	public static function validateUnitCompatibility($fromUnit, $toUnit, $variable = null){
		if(!$toUnit){
			le('!$toUnit');
		}
		if(!is_object($fromUnit)){
			$fromUnit = static::find($fromUnit);
		}
		if(!is_object($toUnit)){
			$toUnit = static::find($toUnit);
		}
		if($fromUnit->id === $toUnit->id){
			return;
		}
		if($fromUnit->isYesNo() && $toUnit->isRating()){
			return;
		}
		if($toUnit->isYesNo() && $fromUnit->isRating()){
			return;
		}
		if($fromUnit->isYesNo() && $toUnit->isCountCategory()){
			return;
		}
		if($toUnit->isYesNo() && $fromUnit->isCountCategory()){
			return;
		}
		if($fromUnit->categoryName !== MiscellanyUnitCategory::NAME &&
			$fromUnit->categoryName === $toUnit->categoryName){
			return;
		}
		throw new IncompatibleUnitException($fromUnit, $toUnit, $variable);
	}
	/**
	 * @return QMUnitCategory
	 */
	public function getUnitCategory(): QMUnitCategory{
		return QMUnitCategory::find($this->categoryName);
	}
	/**
	 * @return string
	 */
	public function getAbbreviatedName(): string{
		return $this->abbreviatedName;
	}
	/**
	 * @param int|string|QMUnit $nameOrId
	 * @param bool $throwException
	 * @return QMUnit
	 */
	public static function getByNameOrId($nameOrId, bool $throwException = true): ?QMUnit{
		if($nameOrId === null){
			QMLog::error("No unit nameOrId provided!");
			return null;
		}
		$isString = is_string($nameOrId);
		// What was this for? Can't get 0 to 5 Rating if($isString && str_starts_with($nameOrId, '0')){return null;}
		if(is_int($nameOrId) || is_numeric($nameOrId)){
			return self::getUnitById($nameOrId, $throwException);
		}
		if($isString){
			return self::findByNameOrSynonym($nameOrId, $throwException);
		}
		if($nameOrId instanceof Unit){
			return $nameOrId->getDBModel();
		}
		return $nameOrId; //Probably already a unit object
	}
	/**
	 * @param float $value
	 * @param $analyzable
	 * @throws InvalidVariableValueException
	 */
	public function throwExceptionIfValueNotValidForUnit(float $value, $analyzable){
		if($this->minimumValue !== null && $value < $this->minimumValue){
			throw new InvalidVariableValueException("$value too small for unit $this->name", $analyzable);
		}
		if($this->maximumValue !== null && $value > $this->maximumValue){
			throw new InvalidVariableValueException("$value too large for unit $this->name", $analyzable);
		}
	}
	/**
	 * @param float $value
	 * @param DBModel|QMAnalyzableTrait $analyzable
	 * @param int|null $durationInSeconds
	 * @return bool
	 * @throws InvalidVariableValueException
	 */
	public function validateValue(float $value, $analyzable = null, int $durationInSeconds = null): bool{
		$this->validateTooSmall($value, $analyzable);
		$this->validateTooBig($value, $analyzable, $durationInSeconds);
		return true;
	}
	/**
	 * @param float $value
	 * @param int|null $durationInSeconds
	 * @return string|null
	 */
	public function valueInvalid(float $value, int $durationInSeconds = null): ?string{
		$message = $this->tooSmall($value);
		if(!$message){
			$message = $this->tooBig($value, $durationInSeconds);
		}
		return $message;
	}
	/**
	 * @param float $value
	 * @return string|null
	 */
	public function tooSmall(float $value): ?string{
		if($this->minimumValue === null){
			return null;
		}
		$intValue = (int)$value; // Float compare says 10.0 is greater than 10.0
		$tooSmall = $intValue < (int)$this->minimumValue;
		if($tooSmall){
			return "$value too small for $this.  Minimum is $this->minimumValue. ";
		}
		return null;
	}
	/**
	 * @param float $value
	 * @param int|null $durationInSeconds
	 * @return string|null
	 */
	public function tooBig(float $value, int $durationInSeconds = null): ?string{
		if($this->isYesNo()){ // Yes/No can be aggregated as count
			return null;
		}
		$maxDaily = $this->maximumDailyValue;
		if($maxDaily && $durationInSeconds){
			$days = $durationInSeconds / 86400;
			$perDay = $value / $days;
			$tooBig = $perDay > $maxDaily;
			if($tooBig){
				return "Daily value $value too big for $this.  Maximum daily value is $maxDaily";
			}
		}
		if($this->maximumValue === null){
			return null;
		}
		$intValue = (int)$value; // Float compare says 10.0 is greater than 10.0
		$max = (int)$this->maximumValue;
		$tooBig = $intValue > $max;
		if($tooBig){
			return "$value too big for $this.  Maximum value is $max";
		}
		return null;
	}
	/**
	 * @param float $value
	 * @param null|QMAnalyzableTrait|\App\Slim\Model\DBModel $analyzable
	 * @return void
	 * @throws InvalidVariableValueException
	 */
	public function validateTooSmall(float $value, $analyzable = null){
		//if(!is_numeric($value)){le('!is_numeric($value)');}
		$tooSmall = $this->tooSmall($value);
		if($tooSmall){
			throw new InvalidVariableValueException($tooSmall, $analyzable);
		}
	}
	/**
	 * @param float $value
	 * @param null|QMAnalyzableTrait|\App\Slim\Model\DBModel $analyzable
	 * @param int|null $durationInSeconds
	 * @throws InvalidVariableValueException
	 */
	public function validateTooBig(float $value, $analyzable = null, int $durationInSeconds = null){
		if($this->isYesNo()){ // Yes/No can be aggregated as count
			return;
		}
		$tooBig = $this->tooBig($value, $durationInSeconds);
		if($tooBig){
			throw new InvalidVariableValueException($tooBig, $analyzable);
		}
	}
	/**
	 * @return bool
	 */
	public function canBeSummed(): bool{
		if($co = $this->combinationOperation){
			return $co === BaseCombinationOperationProperty::COMBINATION_SUM;
		}
		return $this->getUnitCategory()->canBeSummed;
	}
	/**
	 * @param $string
	 * @return null|QMUnit
	 */
	public static function getUnitFromString($string){
		$unitName = UnitNameProperty::fromString($string);
		if($unitName){
			try {
				return self::getByNameOrId($unitName);
			} catch (NotFoundException $e) {
				return null;
			}
		}
		return null;
	}
	/**
	 * @param string $variableName
	 * @param bool $preferNonMiscellaneous
	 * @return int
	 */
	public static function getUnitIdFromString(string $variableName, bool $preferNonMiscellaneous = true): ?int{
		$unitName = UnitNameProperty::fromString($variableName, $preferNonMiscellaneous);
		if($unitName){
			$unit = self::getByNameOrId($unitName);
			if($unit){
				return $unit->id;
			}
		}
		return null;
	}
	/**
	 * @return array
	 */
	public function getConversionSteps(): array{
		return $this->conversionSteps;
	}
	public function getFillingType(): string{
		return $this->fillingType;
	}
	/**
	 * @return mixed
	 */
	public function getMaximumAggregatedValue(): ?float{
		if($this->name === YesNoUnit::NAME){
			return null;
		}
		return $this->maximumValue;
	}
	/**
	 * @param $lowerCaseString
	 * @return float|null
	 */
	private function getNumberBeforePerServing($lowerCaseString): ?float{
		if(stripos($lowerCaseString, 'per serving') !==
			false){ // Needed for Nutricost Psyllium Husk Powder 500 Grams, 5g Per Serving
			$beforePerServing = QMStr::before('per serving', $lowerCaseString);
			$lastWord = QMStr::getLastWord($beforePerServing);
			if(is_numeric($lastWord)){
				return (float)$lastWord;
			}
			return $this->getNumberBeforeUnitNameOrAbbreviatedName($lastWord);
		}
		return null;
	}
	/**
	 * @param $string
	 * @param null $unitName
	 * @return float
	 */
	public function getNumberBeforeUnitNameOrAbbreviatedName($string, $unitName = null): ?float{
		if($unitName){
			return QMStr::getNumberBeforeSubString($string, $unitName);
		}
		$lowerCaseString = strtolower($string);
		$numberBeforePerServing = $this->getNumberBeforePerServing($lowerCaseString);
		if($numberBeforePerServing){
			return $numberBeforePerServing;
		}
		if(stripos($string, $this->getSingularName()) !== false){
			return QMStr::getNumberBeforeSubString($string, $this->getSingularName());
		}
		if(stripos($string, $this->abbreviatedName) !== false){
			return QMStr::getNumberBeforeSubString($string, $this->abbreviatedName);
		}
		foreach($this->getOrGenerateSynonyms() as $synonym){
			if(stripos($string, $synonym) !== false){
				return QMStr::getNumberBeforeSubString($string, $synonym);
			}
		}
		return null;
	}
	/**
	 * @return string
	 */
	private function getSingularName(): string{
		if(strlen($this->name) > 3){
			$lastCharacter = QMStr::getLastCharacter($this->name);
			if($lastCharacter === "s"){
				return QMStr::removeLastCharacter($this->name);
			}
		}
		return $this->name;
	}
	/**
	 * @param float $value
	 * @param int $toUnitId
	 * @param QMAnalyzableTrait|QMVariable $v
	 * @param int|null $durationInSeconds
	 * @return float
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	public function convertTo(float $value, int $toUnitId, $v, int $durationInSeconds = null){
		return self::convertValueByUnitIds($value, $this->id, $toUnitId, $v, $durationInSeconds);
	}
	/**
	 * @param string $valueAndUnitString
	 * @return string
	 */
	public static function formatValueAndUnitText(string $valueAndUnitString): string{
		$valueAndUnitString = self::removeSpaceBeforeSlash($valueAndUnitString);
		$valueAndUnitString = str_replace([
			' %',
			'1 yes/no',
			'0 yes/no',
		], [
			'%',
			'YES',
			'NO',
		], $valueAndUnitString);
		return $valueAndUnitString;
	}
	/**
	 * @param float|string $value
	 * @param bool $useAbbreviatedName
	 * @param int|null $precision
	 * @return string
	 */
	public function getValueAndUnitString($value, bool $useAbbreviatedName = false, int $precision = null): ?string{
		if($value === null){
			return null;
		}
		if($precision){
			$value = Stats::roundByNumberOfSignificantDigits($value, $precision);
		}
		$stringValue = (string)$value;
		if(isset(self::$valueAndUnitStrings[$this->id][$useAbbreviatedName][$stringValue])){
			return self::$valueAndUnitStrings[$this->id][$useAbbreviatedName][$stringValue];
		}
		if(is_string($value)){
			$value = (float)$value;
		}
		if($this->abbreviatedName === "$"){
			$valueAndUnitString = "$" . $value;
		} elseif(!$useAbbreviatedName && $this->maximumValue && $this->isRating()){
			$valueAndUnitString = $value . " out of " . $this->maximumValue;
		} elseif($this->isYesNo()){
			if($value){
				return "Yes";
			} else{
				return "No";
			}
		} elseif($this->isSeconds()){
			$valueAndUnitString = TimeHelper::convertSecondsToHumanString($value);
		} elseif($this->name === MinutesUnit::NAME){
			$valueAndUnitString = TimeHelper::convertSecondsToHumanString($value * 60);
		} elseif($this->name === HoursUnit::NAME){
			$valueAndUnitString = TimeHelper::convertSecondsToHumanString($value * 3600);
		} else{
			$valueAndUnitString =
				$useAbbreviatedName ? $value . " " . $this->abbreviatedName : $value . " " . strtolower($this->name);
			$valueAndUnitString = self::formatValueAndUnitText($valueAndUnitString);
		}
		return self::$valueAndUnitStrings[$this->id][$useAbbreviatedName][$stringValue] = $valueAndUnitString;
	}
	/**
	 * @param float|string $value
	 * @param bool $useAbbreviatedName
	 * @return string
	 */
	public function getHigherLowerValueUnitString($value, bool $useAbbreviatedName = false): string{
		$string = $this->getValueAndUnitString($value, $useAbbreviatedName);
		if(!str_contains($string, '-')){
			$string .= ' higher';
		} else{
			$string .= ' lower';
		}
		$string = str_replace('-', '', $string);
		return $string;
	}
	/**
	 * @return bool
	 */
	private function isSeconds(): bool{
		return $this->abbreviatedName === SecondsUnit::ABBREVIATED_NAME;
	}
	/**
	 * @param $arrayOfStrings
	 * @param $variableCategoryName
	 * @param null $fallbackUnitAbbreviatedName
	 * @return QMUnit
	 */
	public static function getUnitFromArrayOfStrings($arrayOfStrings, $variableCategoryName,
		$fallbackUnitAbbreviatedName = null): ?QMUnit{
		foreach($arrayOfStrings as $value){
			if(self::getUnitByFullName($value)){
				return self::getUnitByFullName($value);
			} // Preference to longer more unique names
		}
		foreach($arrayOfStrings as $value){
			if($variableCategoryName === TreatmentsVariableCategory::NAME &&
				strtolower($value) === "s"){
				continue; // Avoids always using letter S as seconds in MedHelper
			}
			if(!empty($value) && self::getUnitByAbbreviatedName($value)){
				return self::getUnitByAbbreviatedName($value);
			}
		}
		if($fallbackUnitAbbreviatedName){
			return self::getUnitByAbbreviatedName($fallbackUnitAbbreviatedName);
		}
		return null;
	}
	/**
	 * @return QMUnit
	 */
	public static function getCount(): QMUnit{
		return self::getByNameOrId(CountUnit::NAME);
	}
	/**
	 * @return QMUnit
	 */
	public static function getDollars(): QMUnit{
		return self::getByNameOrId(DollarsUnit::NAME);
	}
	/**
	 * @return QMUnit
	 */
	public static function getOneToFiveRating(): QMUnit{
		return self::getByNameOrId(OneToFiveRatingUnit::NAME);
	}
	/**
	 * @return QMUnit
	 */
	public static function getServing(): QMUnit{
		return self::getByNameOrId(ServingUnit::NAME);
	}
	/**
	 * @return QMUnit
	 */
	public static function getYesNo(): QMUnit{
		return self::getByNameOrId(YesNoUnit::NAME);
	}
	/**
	 * @return bool
	 */
	public function isCountCategory(): bool{
		return $this->getUnitCategory()->name === CountUnitCategory::NAME;
	}
	/**
	 * @return bool
	 */
	public function isDurationCategory(): bool{
		return $this->getUnitCategory()->name === DurationUnitCategory::NAME;
	}
	/**
	 * @return bool
	 */
	public function isWeightCategory(): bool{
		return $this->getUnitCategory()->name === WeightUnitCategory::NAME;
	}
	/**
	 * @return bool
	 */
	public function isWeightCategoryOrInternationalUnits(): bool{
		return $this->isWeightCategory() || $this->name === InternationalUnitsUnit::NAME;
	}
	/**
	 * @return bool
	 */
	public function isCurrency(): bool{
		return $this->getUnitCategory()->name === CurrencyUnitCategory::NAME;
	}
	/**
	 * @return string
	 */
	public function getCombinationOperation(): string{
		if($co = $this->combinationOperation){
			return $co;
		} // Don't set combinationOperation from category
		return $this->getUnitCategory()->combinationOperation;
	}
	/**
	 * @return mixed
	 */
	public function getFillingValueAttribute(): ?float{
		/** @noinspection TypeUnsafeComparisonInspection */
		if($this->fillingValue != -1){
			return $this->fillingValue;
		}
		return $this->getUnitCategory()->fillingValue;
	}
	/**
	 * @param string|int $nameOrId
	 * @return QMUnit
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection
	 */
	public static function find($nameOrId): ?DBModel{
		if($nameOrId instanceof QMUnit){
			return $nameOrId;
		}
		if($nameOrId instanceof Unit){
			return $nameOrId->getDBModel();
		}
		if(is_numeric($nameOrId)){
			return self::getUnitById($nameOrId);
		}
		if(is_string($nameOrId)){
			return self::findByNameOrSynonym($nameOrId);
		}
		le("$nameOrId not string or numeric");
		throw new \LogicException();
	}
	/**
	 * @param array $params
	 * @return self[]
	 */
	public static function get(array $params = []): array{
		$units = self::getUnits();
		$units = QMArr::getElementsMatchingRequestParams($params, $units);
		return $units;
	}
	/**
	 * @return bool
	 */
	public function isCount(): bool{
		return $this->abbreviatedName === CountUnit::ABBREVIATED_NAME;
	}
	/**
	 * @return string
	 */
	public function getSuffix(): ?string{
		return $this->suffix;
	}
	/**
	 * @return float
	 */
	public function getDefaultValue(): float{
		return $this->defaultValue;
	}
	/**
	 * @return bool
	 */
	public function isPercent(): bool{
		return $this->name === PercentUnit::NAME;
	}
	/**
	 * @param bool $return100ForPercent
	 * @return float
	 */
	public function getMaximumRawValue(bool $return100ForPercent = false): ?float{
		if($return100ForPercent && $this->isPercent()){
			return (float)100;
		}
		if($this->maximumValue === null){
			return null;
		}
		return (float)$this->maximumValue;
	}
	/**
	 * @return string
	 */
	public function getLogMetaDataString(): string{
		return $this->name;
	}
	/**
	 * @param bool $return0ForPercent
	 * @return float
	 */
	public function getMinimumValue(bool $return0ForPercent = false): ?float{
		if($return0ForPercent && $this->isPercent()){
			return 0.0;
		}
		if($this->minimumValue === null){
			return null;
		}
		return (float)$this->minimumValue;
	}
	/**
	 * @return string
	 */
	public function getHint(): string{
		if($this->hint){
			return $this->hint;
		}
		$hint = "a number ";
		if($this->isYesNo()){
			$hint = "yes or no";
		} elseif($this->minimumValue !== null && $this->maximumValue !== null){
			$hint .= "from " . $this->minimumValue . " to " . $this->maximumValue;
		} elseif($this->minimumValue !== null){
			$hint .= "above or equal to " . $this->minimumValue;
		} elseif($this->maximumValue !== null){
			$hint .= "below or equal to " . $this->maximumValue;
		}
		return $this->hint = $hint;
	}
	/**
	 * @return string
	 */
	public function getNameAttribute(): string{
		return $this->name;
	}
	/**
	 * @return bool
	 */
	public function isRatioScale(): bool{
		return $this->scale === self::SCALE_RATIO;
	}
	/**
	 * @param string $variableName
	 * @throws InvalidVariableNameException
	 */
	public function validateVariableNameForUnit(string $variableName){
		/** @noinspection SpellCheckingInspection */
		if($this->isRatioScale() && stripos($variableName, "roductive Score") !== false){
			throw new InvalidVariableNameException($variableName,
				"Variables with the unit $this should not have the word score in the name! " .
				"Only variables with interval scale units such as ratings and percentages can include the word score. ");
		}
	}
	public function getTitleAttribute(): string{
		return $this->getNameAttribute();
	}
	public function getImage(): string{
		if($this->image && $this->image !== Unit::DEFAULT_IMAGE){
			return $this->image;
		}
		return $this->image = $this->getUnitCategory()->getImage();
	}
	public function getFontAwesome(): string{
		if($this->fontAwesome){
			return $this->fontAwesome;
		}
		return $this->fontAwesome = $this->getUnitCategory()->getFontAwesome();
	}
	public function getSubtitleAttribute(): string{
		$lClass = static::getLaravelClassName();
		return $lClass::getClassDescription();
	}
	/**
	 * @return QMUnit[]
	 */
	public static function getAll(): array{
		return static::getUnits();
	}
	/**
	 * @return Unit
	 */
	public function l(): Unit{
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $this->attachedOrNewLaravelModel();
	}
	/**
	 * @param $nameOrId
	 * @return QMUnit|null
	 */
	public static function findByNameIdOrSynonym($nameOrId){
		if(is_int($nameOrId)){
			return static::find($nameOrId);
		}
		return static::findByNameOrSynonym($nameOrId);
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
	protected static function getMemoryPrimaryKey(): string{ return QMStr::toShortClassName(QMUnit::class); }
}
