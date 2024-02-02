<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\View\Request\Variable;
use App\Http\Parameters\SortParam;
use App\Logging\QMLog;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Models\VariableCategory;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\QMUnit;
use App\Slim\Model\User\QMUser;
use App\Storage\DB\QMQB;
use App\Storage\DB\ReadonlyDB;
use App\Storage\DB\Writable;
use App\Storage\QueryBuilderHelper;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Utils\APIHelper;
use App\Utils\AppMode;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use App\Variables\QMUserVariableV3;
use App\Variables\QMVariable;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
/** Class GetUserVariableRequest
 * @package App\Slim\View\Request\Variable
 */
class GetUserVariableRequest extends GetVariableRequest {
	/** @var array */
	private static $columnsArray;
	private $childUserTagVariable;
	private $childUserTagVariableIds;
	private $eligibleTagVariables;
	private $ingredientOfUserTagVariable;
	private $ingredientOfUserTagVariableIds;
	private $ingredientUserTagVariable;
	private $ingredientUserTagVariableIds;
	private $joinedUserTagVariable;
	private $joinedUserTagVariableIds;
	private $parentUserTagVariable;
	private $parentUserTagVariableIds;
	private $userTaggedVariable;
	private $userTaggedVariableId;
	private $userTaggedVariableIds;
	private $userTagVariable;
	private $userTagVariableIds;
	public $unitCategoryName;
	public $exactMatch;
	public $excludeVariableCategoryId;
	public $fallbackToAggregatedCorrelations;
	public $includePublic;
	public $includeTags;
	public $limit;
	public $manualTracking;
	public $name;
	public $offset;
	public $outcome;
	public $searchPhrase;
	public $shareUserMeasurements;
	public $sort;
	public $statusNotUpdated;
	public $userId;
	public $useWritableConnection;
	public $variableName;
	private $tagSearch;
	private $userVariables;
	public static $aliasToFieldNameMap = [
		'createdAt' => UserVariable::TABLE . '.created_at',
		'id' => UserVariable::TABLE . '.variable_id',
		'lastUpdated' => UserVariable::TABLE . '.updated_at',
		'mostCommonConnectorId' => Variable::TABLE . '.' . Variable::FIELD_MOST_COMMON_CONNECTOR_ID,
		'numberOfChanges' => UserVariable::TABLE . '.number_of_changes',
		'numberOfCorrelationsAsCause' => UserVariable::TABLE . '.number_of_user_variable_relationships_as_cause',
		'numberOfCorrelationsAsEffect' => UserVariable::TABLE . '.number_of_user_variable_relationships_as_effect',
		'numberOfMeasurements' => UserVariable::TABLE . '.number_of_measurements',
		'numberOfProcessedDailyMeasurements' => UserVariable::TABLE . '.number_of_processed_daily_measurements',
		'numberOfRawMeasurements' => UserVariable::TABLE . '.number_of_measurements',
		'numberOfUniqueDailyValues' => UserVariable::TABLE . '.number_of_unique_daily_values',
		'numberOfUniqueValues' => UserVariable::TABLE . '.number_of_unique_values',
		'shareUserMeasurements' => UserVariable::TABLE . '.is_public',
		'sourceName' => UserVariable::TABLE . '.' . Variable::FIELD_DATA_SOURCES_COUNT,
		'updatedAt' => UserVariable::TABLE . '.updated_at',
		'updatedTime' => UserVariable::TABLE . '.updated_at',
		'variableId' => UserVariable::TABLE . '.variable_id',
		'latestMeasurementStartAt' => UserVariable::TABLE . '.' .
			UserVariable::FIELD_LATEST_TAGGED_MEASUREMENT_START_AT,
		//'deletedAt' => UserVariable::TABLE.'.deleted_at', // Already added to query
		//'status' => UserVariable::TABLE.'.status',
		//'variableCategoryId' => CommonVariable::TABLE.'.variable_category_id',
	];
	/**
	 * GetVariableRequest constructor.
	 * @param array $requestParams
	 * @param int $userId
	 * @internal param int $userId
	 */
	public function __construct(array $requestParams, $userId){
		if($userId !== "all"){
			$this->userId = $userId;
		}
		$this->setRequestParams($requestParams);
		parent::__construct(null, $this->requestParams);
		$this->handleAllTagTypes();
	}
	/**
	 * @param int $userId
	 * @param string $status
	 * @return QMUserVariable[]
	 */
	public static function getByStatus(int $userId, string $status): array{
		$getUserVariablesRequest = new GetUserVariableRequest([], $userId);
		$getUserVariablesRequest->setUserId($userId);
		$getUserVariablesRequest->setLimit(0);
		$getUserVariablesRequest->setStatus($status);
		$variables = $getUserVariablesRequest->getVariables();
		return $variables;
	}
	/**
	 * @param $requestParams
	 */
	private function setRequestParams($requestParams){
		$requestParams = static::properlyFormatRequestParams($requestParams, self::getLegacyRequestParameters());
		$this->validateRequestParams($requestParams);
		foreach($requestParams as $key => $value){
			$this->$key = $value;
			/** @noinspection TypeUnsafeComparisonInspection */
			if($this->$key == ""){
				$this->$key = null;
			}
		}
		$this->requestParams = $requestParams;
	}
	/**
	 * @return int
	 */
	public function getUserId(): ?int{
		return $this->userId ?? QMAuth::id();
	}
	/**
	 * @return QMUserVariable[]
	 */
	public function getEligibleTagVariables(): array{
		return $this->eligibleTagVariables;
	}
	private function removeAlreadyTaggedAndIncompatibleForAllTagTypes(){
		$this->removeAlreadyTaggedVariablesOrIncompatibleUnit($this->getUserTagVariableIds(),
			$this->userTaggedVariable);
		$this->removeAlreadyTaggedVariablesOrIncompatibleUnit($this->getUserTaggedVariableIds(),
			$this->userTagVariable);
		$this->removeAlreadyTaggedVariablesOrIncompatibleUnit($this->getIngredientUserTagVariableIds(),
			$this->ingredientOfUserTagVariable);
		$this->removeAlreadyTaggedVariablesOrIncompatibleUnit($this->getIngredientOfUserTagVariableIds(),
			$this->ingredientUserTagVariable);
		$this->removeAlreadyTaggedVariablesOrIncompatibleUnit($this->getParentUserTagVariableIds(),
			$this->childUserTagVariable);
		$this->removeAlreadyTaggedVariablesOrIncompatibleUnit($this->getChildUserTagVariableIds(),
			$this->parentUserTagVariable);
		$this->removeAlreadyTaggedVariablesOrIncompatibleUnit($this->getJoinedUserTagVariableIds(),
			$this->joinedUserTagVariable);
	}
	/**
	 * @param QMUserVariable[] $userVariables
	 * @param string $unitCategoryName
	 * @return array
	 */
	public static function removeVariablesWithDifferentUnitCategory(array $userVariables,
		string $unitCategoryName): array{
		$variablesToKeep = [];
		foreach($userVariables as $userVariable){
			if($userVariable->getUserOrCommonUnit()->categoryName === $unitCategoryName){
				$variablesToKeep[] = $userVariable;
			}
		}
		return $variablesToKeep;
	}
	/**
	 * @param array $existingTagVariableIds
	 * @param \App\Variables\QMUserVariable|null $targetVariable
	 */
	public function removeAlreadyTaggedVariablesOrIncompatibleUnit(array $existingTagVariableIds,
		?QMUserVariable $targetVariable){
		if(!$targetVariable){
			return;
		}
		if(!$existingTagVariableIds){
			return;
		}
		$this->eligibleTagVariables =
			self::removeVariablesWithIdInArray($this->eligibleTagVariables, $existingTagVariableIds);
		$this->removeIncompatibleRatingTagVariables($targetVariable);
		if($this->joinedUserTagVariable){
			$this->eligibleTagVariables = self::removeVariablesWithDifferentUnitCategory($this->eligibleTagVariables,
				$targetVariable->getUserOrCommonUnit()->categoryName);
		}
	}
	/**
	 * @param string $searchPhrase
	 * @param QMUser $user
	 * @return array|QMUserVariable[]
	 */
	public static function getUserVariablesSimple(string $searchPhrase, QMUser $user): array{
		$qb = self::qb();
		QMUserVariable::addLimitOffsetSort($qb, UserVariable::TABLE);
		$qb->where(UserVariable::TABLE . '.user_id', $user->id);
		GetVariableRequest::addWhereClauseForEachWord($searchPhrase, $qb);
		GetVariableRequest::addCategoryClauseIfNecessary($searchPhrase, $qb);
		QueryBuilderHelper::applyFilterParamsIfExist($qb, self::$aliasToFieldNameMap, $_GET);
		$sort = SortParam::getSort();
		if(!$sort){$sort = QMUserVariable::DEFAULT_SORT_FIELD;}
		$ids = $qb->pluck(UserVariable::FIELD_ID);
		$userVariableQB = UserVariable::with('variable');
		QMUserVariable::addSortToQb($userVariableQB, UserVariable::TABLE, $sort);
		$models = $userVariableQB->find($ids);
		$userVariables = UserVariable::toDBModels($models);
		if($sort){
			self::addSubTitles($userVariables, $sort);
		}
//		QMArr::sortDescending($userVariables,
//			'latestTaggedMeasurementTime');  // May be overridden by request sort later
//		QMArr::sortDescending($userVariables, 'numberOfTrackingReminders'); // May be overridden by request sort later
		$userVariables = self::getExactMatchIfNecessary($searchPhrase, $user, $userVariables);
		return $userVariables;
	}
	/**
	 * @param QMUserVariable[] $userVariables
	 * @param array $variableIds
	 * @return QMUserVariable[]
	 */
	public static function removeVariablesWithIdInArray(array $userVariables, array $variableIds): array{
		$variablesToKeep = [];
		foreach($userVariables as $v){
			$id = $v->variableId;
			if(!in_array($id, $variableIds, true)){
				$variablesToKeep[] = $v;
			}
		}
		return $variablesToKeep;
	}
	/**
	 * @param QMUserVariable $targetVariable
	 */
	private function removeIncompatibleRatingTagVariables(QMUserVariable $targetVariable){
		$compatibleVariables = [];
		foreach($this->getEligibleTagVariables() as $eligibleTagVariable){
			if($targetVariable->isRating() && $eligibleTagVariable->isRating()){
				$compatibleVariables[] = $eligibleTagVariable;
			}
			if(!$targetVariable->isRating() && !$eligibleTagVariable->isRating()){
				$compatibleVariables[] = $eligibleTagVariable;
			}
		}
		$this->eligibleTagVariables = $compatibleVariables;
	}
	/**
	 * @param string $searchPhrase
	 * @param QMUser $user
	 * @param $variables
	 * @return array|QMCommonVariable[]|QMVariable[]|QMUserVariable[]
	 */
	private static function getExactMatchIfNecessary(string $searchPhrase, QMUser $user, $variables): array{
		$exactMatch = QMVariable::getExactMatchFromArray($variables, $searchPhrase);
		if($exactMatch){
			$variables = QMVariable::putExactMatchFirst($variables, $searchPhrase);
		} else{
			if(count($variables) < QMUserVariable::getLimitFromRequestOrModelDefault() - 1){
				return $variables;
			}
			$exactMatch = QMUserVariable::findByName($user->getId(), $searchPhrase);
			if($exactMatch){
				$variables = array_merge([$exactMatch], $variables);
			}
		}
		return $variables;
	}
	/**
	 * @param $value
	 * @return string|string[]
	 */
	public static function replaceLegacySort($value){
		if($value){
			$value = str_replace('TaggedMeasurementTime', 'TaggedMeasurementStartAt', $value);
		}
		if($value){
			$value = str_replace('latestMeasurementTime', 'latestTaggedMeasurementStartAt', $value);
		}
		return $value;
	}
	/**
	 * @return array
	 */
	private function getCommonVariableRequestParams(): array{
		$requestParams = $this->getRequestParams();
		return $requestParams;
	}
	/**
	 * @return bool
	 */
	public function isFallbackToAggregatedCorrelations(): ?bool{
		return $this->fallbackToAggregatedCorrelations;
	}
	/**
	 * @param array $requestParams
	 * @internal param bool $fallbackToAggregatedCorrelations
	 */
	public function setFallbackToAggregatedCorrelations(array $requestParams){
		$this->fallbackToAggregatedCorrelations = $requestParams['fallbackToAggregatedCorrelations'] ?? false;
	}
	/**
	 * @param string|null $sort
	 * @return string
	 */
	public function setSort(string $sort = null): ?string{
		if($sort){
			return $this->sort = $sort;
		}
		if($this->getNumberOfCorrelationsAsCause()){
			$this->sort = '-numberOfCorrelationsAsCause';
			$this->outcome = false;
		}
		if($this->getNumberOfCorrelationsAsEffect()){
			$this->sort = '-numberOfCorrelationsAsEffect';
			$this->outcome = false;
		}
		$this->requestParams['sort'] = $this->sort;
		return $this->sort;
	}
	/**
	 * @param int $userId
	 * @param int $userTaggedVariableId
	 * @return QMUserVariable
	 */
	public function setUserTaggedVariable(int $userId, int $userTaggedVariableId): QMUserVariable{
		$tagged = QMUserVariable::getOrCreateById($userId, $userTaggedVariableId, ['includeTags' => true]);
		if($userTaggedVariableId && !$tagged){
			QMLog::error("No user tagged variable for id $userTaggedVariableId");
		}
		return $this->userTaggedVariable = $tagged;
	}
	/**
	 * @param int|null $userTaggedVariableId
	 */
	public function setUserTaggedVariableId(?int $userTaggedVariableId){
		$this->userTaggedVariableId = $userTaggedVariableId;
	}
	/**
	 * @return bool
	 */
	public function isIncludePublic(): ?bool{
		return $this->includePublic;
	}
	/**
	 * @param bool $includePublic
	 */
	public function setIncludePublic(bool $includePublic){
		$this->includePublic = $includePublic;
	}
	/**
	 * @return array
	 */
	private function getUserTagVariableIds(): array{
		if($this->userTagVariableIds !== null){
			return $this->userTagVariableIds;
		}
		if(!isset($this->userTaggedVariableId)){
			return [];
		}
		$this->setIncludePublic(true);
		$this->setIncludeTags(true);
		$v = $this->setUserTaggedVariable($this->getUserId(), $this->userTaggedVariableId);
		$tags = $v->getUserTagVariables();
		foreach($tags as $userTagVariable){
			$this->userTagVariableIds[] = $userTagVariable->getVariableIdAttribute();
		}
		$this->userTagVariableIds[] = $this->userTaggedVariableId;
		$this->setUserTaggedVariableId(null);
		$this->setEligibleTagVariables();
		return $this->userTagVariableIds;
	}
	private function setEligibleTagVariables(): void{
		$this->tagSearch = true;
		$eligibleTagVariableRequest = new self([], $this->getUserId());
		$eligibleTagVariableRequest->setUserId($this->getUserId());
		$eligibleTagVariableRequest->setIncludePublic(true);
		$eligibleTagVariableRequest->setIncludeTags(false);  // I think this will cause infinite loop?
		$searchPhrase = $this->getSearchPhrase();
		if($searchPhrase){
			$eligibleTagVariableRequest->setSearchPhrase($searchPhrase);
		}
		$this->eligibleTagVariables = $eligibleTagVariableRequest->setUserVariables();
		$this->removeAlreadyTaggedAndIncompatibleForAllTagTypes();
	}
	/**
	 * @return array
	 */
	private function getUserTaggedVariableIds(): array{
		if($this->userTaggedVariableIds !== null){
			return $this->userTaggedVariableIds;
		}
		if(!isset($this->userTagVariableId)){
			return [];
		}
		$this->includePublic =
			false;  // Don't get public variables because we're only looking for user variables than can be tagged with this userTagVariableId
		$this->includeTags = true;
		$this->userTagVariable =
		$v = QMUserVariable::getOrCreateById($this->getUserId(), $this->userTagVariableId, ['includeTags' => true]);
		$ids = $v->getUserTaggedVariableIds();
		$ids[] = $this->userTagVariableId;
		$this->userTaggedVariableIds = $ids;
		unset($this->userTagVariableId);
		$this->setEligibleTagVariables();
		return $ids;
	}
	/**
	 * @return array
	 */
	private function getIngredientUserTagVariableIds(): array{
		if($this->ingredientUserTagVariableIds !== null){
			return $this->ingredientUserTagVariableIds;
		}
		if(!isset($this->ingredientOfUserTagVariableId)){
			return [];
		}
		$this->includePublic = true;
		$this->includeTags = true;
		$foodId = $this->ingredientOfUserTagVariableId;
		$food = $this->ingredientOfUserTagVariable =
			QMUserVariable::getOrCreateById($this->getUserId(), $foodId, ['includeTags' => true]);
		$ingredients = $food->getIngredientUserTagVariables();
		foreach($ingredients as $ingredient){
			$this->ingredientUserTagVariableIds[] = $ingredient->getVariableIdAttribute();
		}
		$this->ingredientUserTagVariableIds[] = $this->ingredientOfUserTagVariableId;
		unset($this->ingredientOfUserTagVariableId);
		$this->setEligibleTagVariables();
		return $this->ingredientUserTagVariableIds;
	}
	/**
	 * @return array
	 */
	private function getIngredientOfUserTagVariableIds(): array{
		if($this->ingredientOfUserTagVariableIds !== null){
			return $this->ingredientOfUserTagVariableIds;
		}
		if(!isset($this->ingredientUserTagVariableId)){
			return [];
		}
		$this->includePublic = true;
		$this->includeTags = true;
		$this->ingredientUserTagVariable =
			QMUserVariable::getOrCreateById($this->getUserId(), $this->ingredientUserTagVariableId,
				['includeTags' => true]);
		foreach($this->ingredientUserTagVariable->ingredientOfUserTagVariables as $ingredientOfUserTagVariable){
			/** @var QMUserVariable $ingredientOfUserTagVariable */
			$this->ingredientOfUserTagVariableIds[] = $ingredientOfUserTagVariable->getVariableIdAttribute();
		}
		$this->ingredientOfUserTagVariableIds[] = $this->ingredientUserTagVariableId;
		unset($this->ingredientUserTagVariableId);
		$this->setEligibleTagVariables();
		return $this->ingredientOfUserTagVariableIds;
	}
	/**
	 * @return array
	 */
	private function getParentUserTagVariableIds(): array{
		if($this->parentUserTagVariableIds !== null){
			return $this->parentUserTagVariableIds;
		}
		if(!isset($this->childUserTagVariableId)){
			return [];
		}
		$this->includePublic = true;
		$this->includeTags = true;
		$targetVariable = $this->childUserTagVariable =
			QMUserVariable::getOrCreateById($this->getUserId(), $this->childUserTagVariableId, ['includeTags' => true]);
		foreach($this->childUserTagVariable->parentUserTagVariables as $parentUserTagVariable){
			/** @var QMUserVariable $parentUserTagVariable */
			$this->parentUserTagVariableIds[] = $parentUserTagVariable->getVariableIdAttribute();
		}
		$this->parentUserTagVariableIds[] = $this->childUserTagVariableId;
		unset($this->childUserTagVariableId);
		$this->setEligibleTagVariables();
		$this->eligibleTagVariables = self::removeVariablesWithDifferentUnitCategory($this->eligibleTagVariables,
			$targetVariable->getUserOrCommonUnit()->categoryName);
		return $this->parentUserTagVariableIds;
	}
	/**
	 * @return array
	 */
	private function getChildUserTagVariableIds(): array{
		if($this->childUserTagVariableIds !== null){
			return $this->childUserTagVariableIds;
		}
		if(!isset($this->parentUserTagVariableId)){
			return [];
		}
		$this->includePublic = true;
		$this->includeTags = true;
		$targetVariable = $this->parentUserTagVariable =
			QMUserVariable::getOrCreateById($this->getUserId(), $this->parentUserTagVariableId,
				['includeTags' => true]);
		foreach($this->parentUserTagVariable->childUserTagVariables as $childUserTagVariable){
			/** @var QMUserVariable $childUserTagVariable */
			$this->childUserTagVariableIds[] = $childUserTagVariable->getVariableIdAttribute();
		}
		$this->childUserTagVariableIds[] = $this->parentUserTagVariableId;
		unset($this->parentUserTagVariableId);
		$this->setEligibleTagVariables();
		$this->eligibleTagVariables = self::removeVariablesWithDifferentUnitCategory($this->eligibleTagVariables,
			$targetVariable->getUserOrCommonUnit()->categoryName);
		return $this->childUserTagVariableIds;
	}
	/**
	 * @return array
	 */
	private function getJoinedUserTagVariableIds(): array{
		if($this->joinedUserTagVariableIds !== null){
			return $this->joinedUserTagVariableIds;
		}
		if(!isset($this->joinVariableId)){
			return [];
		}
		$this->includePublic = true;
		$this->includeTags = true;
		$this->joinedUserTagVariable =
			QMUserVariable::getOrCreateById($this->getUserId(), $this->joinVariableId, ['includeTags' => true]);
		$joinedUserTagVariables = $this->joinedUserTagVariable->joinedUserTagVariables;
		foreach($joinedUserTagVariables as $joinedUserTagVariable){
			/** @var QMUserVariable $joinedUserTagVariable */
			$this->joinedUserTagVariableIds[] = $joinedUserTagVariable->getVariableIdAttribute();
		}
		$this->joinedUserTagVariableIds[] = $this->joinVariableId;
		unset($this->joinVariableId);
		$this->setEligibleTagVariables();
		return $this->joinedUserTagVariableIds;
	}
	private function handleAllTagTypes(){
		$this->getUserTagVariableIds();
		$this->getUserTaggedVariableIds();
		$this->getIngredientOfUserTagVariableIds();
		$this->getIngredientUserTagVariableIds();
		$this->getParentUserTagVariableIds();
		$this->getChildUserTagVariableIds();
		$this->getJoinedUserTagVariableIds();
	}
	/**
	 * @return bool
	 */
	public function getFallbackToAggregatedCorrelations(): ?bool{
		return $this->fallbackToAggregatedCorrelations;
	}
	/**
	 * @return string
	 */
	public function getUnitCategoryName(): ?string{
		return $this->unitCategoryName;
	}
	/**
	 * @return string
	 */
	public function getSort(): ?string{
		return $this->sort ?: $this->setSort();
	}
	/**
	 * @return GetCommonVariablesRequest
	 */
	public function getCommonVariableRequest(): GetCommonVariablesRequest{
		return new GetCommonVariablesRequest($this->getCommonVariableRequestParams());
	}
	/**
	 * @return array
	 */
	public function getRequestParameterMap(): array{
		return array_merge(self::$aliasToFieldNameMap, $this->getSharedRequestParameterMap());
	}
	/**
	 * @return bool
	 */
	public function getTagSearch(): ?bool{
		return $this->tagSearch;
	}
	/**
	 * @return array
	 */
	public static function getLegacyRequestParameters(): array{
		// Legacy => Current
		return [
			'defaultUnitAbbreviatedName' => 'abbreviatedUnitName',
			'category' => 'variableCategoryName',
			'categoryName' => 'variableCategoryName',
			'defaultUnit' => 'unitId',
			'fallbackToGlobalVariableRelationships' => 'fallbackToAggregatedCorrelations',
			'taggedVariableId' => 'userTaggedVariableId',
			'tagVariableId' => 'userTagVariableId',
			'defaultUnitId' => 'unitId',
			'variableCategory' => 'variableCategoryName',
			'numberOfAggregatedCorrelationsAsCause' => 'numberOfCorrelationsAsCause',
			'numberOfAggregatedCorrelationsAsEffect' => 'numberOfCorrelationsAsEffect',
			'numberOfGlobalVariableRelationshipsAsCause' => 'numberOfCorrelationsAsCause',
			'numberOfGlobalVariableRelationshipsAsEffect' => 'numberOfCorrelationsAsEffect',
			'numberOfUserVariableRelationshipsAsCause' => 'numberOfCorrelationsAsCause',
			'numberOfUserVariableRelationshipsAsEffect' => 'numberOfCorrelationsAsEffect',
			'source' => 'sourceName',
		];
	}
	public static function getCommonVariableFieldMap(): array{
		$arr = [
			Variable::FIELD_ADDITIONAL_META_DATA => 'commonAdditionalMetaData',
			Variable::FIELD_BEST_AGGREGATE_CORRELATION_ID => null,
			Variable::FIELD_BEST_CAUSE_VARIABLE_ID => 'commonBestCauseVariableId',
			Variable::FIELD_BEST_EFFECT_VARIABLE_ID => 'commonBestEffectVariableId',
			Variable::FIELD_DATA_SOURCES_COUNT => 'commonDataSourcesCount',
			Variable::FIELD_MANUAL_TRACKING => null,
			Variable::FIELD_MAXIMUM_ALLOWED_VALUE => 'commonMaximumAllowedValueInCommonUnit',
			Variable::FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS => null,
			Variable::FIELD_MINIMUM_ALLOWED_VALUE => 'commonMinimumAllowedValueInCommonUnit',
			Variable::FIELD_NUMBER_COMMON_TAGGED_BY => null,
			Variable::FIELD_NUMBER_OF_COMMON_TAGS => null,
			Variable::FIELD_NUMBER_OF_MEASUREMENTS => 'commonNumberOfRawMeasurements',
			Variable::FIELD_NUMBER_OF_UNIQUE_VALUES => 'commonNumberOfUniqueValues',
			Variable::FIELD_OPTIMAL_VALUE_MESSAGE => 'commonOptimalValueMessage',
			Variable::FIELD_UPC_14 => 'upc',
			'common_alias' => null,
			'default_unit_id' => 'commonUnitId',
			'description' => null,
			'image_url' => null,
			'informational_url' => null,
			'ion_icon' => null,
			'most_common_connector_id' => null,
			'number_of_global_variable_relationships_as_cause' => null,
			'number_of_global_variable_relationships_as_effect' => null,
			'number_of_user_variables' => null,
			'parent_id' => 'parent',
			'price' => null,
			'product_url' => null,
			'second_most_common_value' => 'secondMostCommonValueInCommonUnit',
			'synonyms' => null,
			'third_most_common_value' => 'thirdMostCommonValueInCommonUnit',
			'updated_at' => 'commonVariableUpdatedAt',
			'valence' => 'commonVariableValence',
			'variable_category_id' => 'commonVariableCategoryId',
			'wikipedia_title' => null,
		];
		foreach($arr as $column => $property){
			if(!$property){
				$arr[$column] = QMStr::camelize($column);
			}
		}
		return $arr;
	}
	public static function getUserVariableFieldMap(): array{
		$arr = [
			'id' => 'userVariableId',
			UserVariable::FIELD_ANALYSIS_SETTINGS_MODIFIED_AT => null,
			UserVariable::FIELD_AVERAGE_SECONDS_BETWEEN_MEASUREMENTS => null,
			UserVariable::FIELD_BEST_CAUSE_VARIABLE_ID => 'userBestCauseVariableId',
			UserVariable::FIELD_BEST_EFFECT_VARIABLE_ID => 'userBestEffectVariableId',
			UserVariable::FIELD_BEST_USER_VARIABLE_RELATIONSHIP_ID => null,
			UserVariable::FIELD_DATA_SOURCES_COUNT => null,
			UserVariable::FIELD_EARLIEST_TAGGED_MEASUREMENT_START_AT => null,
			UserVariable::FIELD_EARLIEST_NON_TAGGED_MEASUREMENT_START_AT => null,
			UserVariable::FIELD_LAST_CORRELATED_AT => null,
			UserVariable::FIELD_LATEST_TAGGED_MEASUREMENT_START_AT => null,
			UserVariable::FIELD_LATEST_NON_TAGGED_MEASUREMENT_START_AT => null,
			UserVariable::FIELD_MEDIAN_SECONDS_BETWEEN_MEASUREMENTS => null,
			UserVariable::FIELD_OPTIMAL_VALUE_MESSAGE => 'userOptimalValueMessage',
			UserVariable::FIELD_OUTCOME_OF_INTEREST => null,
			'alias' => null,
			'client_id' => null,
			'created_at' => null,
			'default_unit_id as userUnitId' => null,
			'earliest_filling_time' => null,
			'experiment_end_time' => null,
			'experiment_start_time' => null,
			'filling_type' => null,
			'filling_value' => null,
			'kurtosis' => null,
			'last_processed_daily_value' => 'lastProcessedDailyValueInCommonUnit',
			'last_value' => 'lastValueInCommonUnit',
			'latest_filling_time' => null,
			'maximum_recorded_value' => 'maximumRecordedValueInCommonUnit',
			'mean' => 'meanInCommonUnit',
			'measurements_at_last_analysis' => 'numberOfRawMeasurementsWithTagsJoinsChildrenAtLastAnalysis',
			'median' => 'medianInCommonUnit',
			'minimum_recorded_value' => 'minimumRecordedValueInCommonUnit',
			'most_common_connector_id' => 'userVariableMostCommonConnectorId',
			'number_of_changes' => null,
			'number_of_correlations' => 'numberOfUserVariableRelationships',
			'number_of_measurements_with_tags_at_last_correlation' => null,
			'number_of_processed_daily_measurements' => null,
			'number_of_measurements' => null,
			'number_of_raw_measurements_with_tags_joins_children' => null,
			'number_of_tracking_reminders' => null,
			'number_of_unique_daily_values' => null,
			'number_of_unique_values' => 'userNumberOfUniqueValues',
			'number_of_user_variable_relationships_as_cause' => null,
			'number_of_user_variable_relationships_as_effect' => null,
			'parent_id' => null,
			'predictor_of_interest' => null,
			'second_to_last_value' => 'secondToLastValueInCommonUnit',
			'is_public' => null,
			'skewness' => null,
			'standard_deviation' => null,
			'status' => null,
			'third_to_last_value' => 'thirdToLastValueInCommonUnit',
			'updated_at' => null,
			'user_id' => null,
			'valence' => 'userVariableValence',
			'variable_category_id' => 'userVariableVariableCategoryId',
			'variable_id' => null,
			'variance' => null,
			'wikipedia_title' => 'userVariableWikipediaTitle',
		];
		foreach($arr as $column => $property){
			if(!$property){
				$arr[$column] = QMStr::camelize($column);
			}
		}
		return $arr;
	}
	/**
	 * @return array
	 */
	public static function getUserVariableColumnsArray(): array{
		if(self::$columnsArray){
			return self::$columnsArray;
		}
		$db = ReadonlyDB::db();
		$cvt = Variable::TABLE;
		$uvt = UserVariable::TABLE;
		$arr = [
			$uvt . '.id',
			$cvt . '.id as variableId',
			$cvt . '.name',
		];
		foreach(self::getUserVariableFieldMap() as $field => $property){
			if(!$property){
				$property = QMStr::camelize($field);
			}
			$arr[] = $uvt . '.' . $field . " as $property";
		}
		foreach(self::getCommonVariableFieldMap() as $field => $property){
			if(!$property){
				$property = QMStr::camelize($field);
			}
			$arr[] = $cvt . '.' . $field . " as $property";
		}
        $cause = $db->raw('IFNULL(' . $uvt . '.cause_only, IFNULL(' . $cvt . '.cause_only, IFNULL(' .
            VariableCategory::TABLE . '.cause_only, NULL))) as causeOnly');
        if(Writable::getConnectionName() == 'pgsql'){
            $cause = $db->raw('COALESCE(' . $uvt . '.cause_only, COALESCE(' . $cvt . '.cause_only, COALESCE(' .
                VariableCategory::TABLE . '.cause_only, NULL))) as causeOnly');
        }
        $arr = array_merge($arr, [
			$db->raw('COALESCE(' . $uvt . '.most_common_value, ' . $cvt .
				'.most_common_value) AS mostCommonValueInCommonUnit'),
			VariableCategory::TABLE . '.' . VariableCategory::FIELD_OUTCOME . ' AS ' .
			VariableCategory::FIELD_OUTCOME,
			$db->raw('COALESCE(' . $uvt . '.onset_delay, ' . $cvt . '.onset_delay, ' . VariableCategory::TABLE .
				'.onset_delay) as onsetDelay'),
			$db->raw('COALESCE(' . $uvt . '.duration_of_action, ' . $cvt . '.duration_of_action, ' .
				VariableCategory::TABLE . '.duration_of_action) as durationOfAction'),
			$db->raw('COALESCE(' . $uvt . '.combination_operation, ' . $cvt .
				'.combination_operation) as combinationOperation'),
            $cause,
			$db->raw('COALESCE(' . $cvt . '.' . Variable::FIELD_IS_PUBLIC . ', ' . VariableCategory::TABLE . '.' .
				VariableCategory::FIELD_IS_PUBLIC . ') as isPublic'),
		]);
		$arr = QMUserVariable::addSelectFields($arr);
		return self::$columnsArray = $arr;
	}
	/**
	 * @param Connection|null $db
	 * @return QMQB
	 */
	public static function qb(Connection $db = null): QMQB{
		if(!$db){
			$db = ReadonlyDB::db();
		}
		return $db->table(Variable::TABLE)->select(self::getUserVariableColumnsArray())
			->leftJoin(VariableCategory::TABLE, Variable::TABLE . '.variable_category_id', '=',
				VariableCategory::TABLE . '.id')
			->join(UserVariable::TABLE, Variable::TABLE . '.id', '=', UserVariable::TABLE . '.variable_id')
			->whereNull(UserVariable::TABLE . '.' . UserVariable::FIELD_DELETED_AT);
	}
	/**
	 * @return QMQB
	 */
	public function complicatedQb(): QMQB{
		$db = $this->getDbConnection();
		$qb = self::qb($db);
		if($this->limit){$qb->limit($this->limit);}
		$this->setFallbackToAggregatedCorrelations($this->requestParams);
		$this->addUserVariableClausesToQb($qb);
		$this->addSharedVariableClausesToQb($qb);
		if(!$qb->orders){
			$qb->orderBy(UserVariable::TABLE . '.latest_tagged_measurement_start_at', 'DESC');
		}
		return $qb;
	}
	/**
	 * @param Builder $qb
	 */
	private function addUserVariableClausesToQb(Builder $qb){
		if($this->getUserId() && $this->getUserId() != 'all'){
			$qb->where(UserVariable::TABLE . '.user_id', '=', $this->getUserId());
		}
		if($this->getExcludeVariableCategoryId()){
			$qb->whereRaw(Variable::TABLE . '.variable_category_id <> ' .
				$this->getExcludeVariableCategoryId());
		}
		if($this->getOutcome()){
			$qb->where(VariableCategory::TABLE . '.' . VariableCategory::FIELD_OUTCOME, $this->outcome);
			$this->setOutcome(null);
		}
		if($this->getStatusNotUpdated()){
			$qb->where(UserVariable::TABLE . '.status', '<>', 'UPDATED');
		}
		$this->replaceNameWithSearchPhraseIfGreaterThan125Characters();
		$params = $this->getRequestParams();
		QueryBuilderHelper::applyFilterParamsIfExist($qb, $this->getRequestParameterMap(), $params);
		$sort = $this->setSort();
		$params['sort'] = self::replaceLegacySort($sort);
		QueryBuilderHelper::applyOffsetLimitSort($qb, $params, self::$aliasToFieldNameMap);
		if(!$qb->orders){
			$qb->orderBy(UserVariable::TABLE . '.latest_tagged_measurement_start_at', 'DESC');
		}
		if($sort){
			if(str_contains($sort, 'numberOfCorrelationsAsEffect')){
				$qb->where(UserVariable::TABLE . '.' . UserVariable::FIELD_NUMBER_OF_USER_VARIABLE_RELATIONSHIPS_AS_EFFECT,
					'>', 0);
			}
			if(str_contains($sort, 'numberOfCorrelationsAsCause')){
				$qb->where(UserVariable::TABLE . '.' . UserVariable::FIELD_NUMBER_OF_USER_VARIABLE_RELATIONSHIPS_AS_CAUSE,
					'>', 0);
			}
		}
		if($this->getStatus()){
			$qb->where(UserVariable::TABLE . '.' . Variable::FIELD_STATUS, $this->getStatus());
		}
	}
	/**
	 * @return QMUser
	 */
	private function getUser(): QMUser{
		return QMUser::find($this->getUserId());
	}
	/**
	 * @return QMUserVariable[]
	 */
	public function setUserVariables(): array{
		$qb = $this->complicatedQb();
		$rows = $qb->getArray();
		if(!$qb->wheres){
			$this->logError("No where clauses on variable query!");
		}
		if(!count($rows)){
			QMLog::debug("Variable not found", ['variable request' => $this->getRequestParams()]);
		}
		$total = count($rows);
		if($total > 200){
			QMLog::info("Got $total user variables rows from database for " .
				$this->getUser()->getLoginNameAndIdString());
		}
		//QMProfile::startProfile();
		$variables = $this->instantiate($rows);
		//QMProfile::endProfileAndSaveResult();
		return $this->userVariables = $variables;
	}
	/**
	 * @param int $userId
	 */
	public function setUserId(int $userId){
		$this->userId = $userId;
	}
	/**
	 * @return QMUserVariable[]
	 */
	public function getVariables(): array{
		return $this->setUserVariables();
	}
	/**
	 * @param int $limit
	 * @return QMUserVariable[]
	 */
	public function getWithTooBigMeanForUnit(int $limit): array{ // Limit needed to avoid memory issues
		$qb = $this->complicatedQb();
		$qb->join(QMUnit::TABLE, Variable::TABLE . '.' . Variable::FIELD_DEFAULT_UNIT_ID, '=',
			QMUnit::TABLE . '.id')->whereNotNull(QMUnit::TABLE . '.maximum_value')->whereRaw(UserVariable::TABLE .
				'.mean > ' . QMUnit::TABLE . '.maximum_value')->limit($limit);
		return $this->userVariables = $this->instantiate($qb->getArray());
	}
	/**
	 * @param $fieldName
	 * @return QMUserVariable[]
	 */
	public function getWithFieldTooSmallForUnit($fieldName): array{
		$qb = $this->complicatedQb();
		$qb->join(QMUnit::TABLE, Variable::TABLE . '.' . Variable::FIELD_DEFAULT_UNIT_ID, '=',
			QMUnit::TABLE . '.id')->whereNotNull(QMUnit::TABLE . '.minimum_value')->whereRaw(UserVariable::TABLE . '.' .
				$fieldName . ' < ' . QMUnit::TABLE . '.minimum_value');
		return $this->userVariables = $this->instantiate($qb->getArray());
	}
	/**
	 * @param string $searchPhrase
	 * @param int $userId
	 * @return null|QMUserVariable
	 */
	public static function getWithNameLike(string $searchPhrase, int $userId): ?QMUserVariable{
		$commonVariables = GetCommonVariablesRequest::getWithNameContainingAllWords($searchPhrase);
		if(!empty($commonVariables)){
			return $commonVariables[0]->findQMUserVariable($userId);
		}
		return null;
	}
	/**
	 * @param $rows
	 * @return array|QMUserVariable[]
	 */
	protected function instantiate($rows): array{
		$variables = QMUserVariable::convertRowsToVariables($rows, $this, SortParam::getSort());
		if($variables && AppMode::isApiRequest() && APIHelper::apiVersionIsBelow(4)){
			$variables = QMUserVariableV3::convert($variables);
		}
		return $variables;
	}
}
