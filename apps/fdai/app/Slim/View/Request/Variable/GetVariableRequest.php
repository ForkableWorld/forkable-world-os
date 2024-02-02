<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\View\Request\Variable;
use App\DataSources\QMConnector;
use App\Exceptions\BadRequestException;
use App\Exceptions\QMException;
use App\Http\Parameters\LimitParam;
use App\Http\Parameters\SortParam;
use App\Logging\QMLog;
use App\Models\Variable;
use App\Models\VariableCategory;
use App\Properties\Base\BaseVariableCategoryIdProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\QMUnit;
use App\Slim\Model\User\QMUser;
use App\Slim\QMSlim;
use App\Slim\View\Request\QMRequest;
use App\Slim\View\Request\Request;
use App\Storage\DB\QMQB;
use App\Storage\DB\ReadonlyDB;
use App\Storage\DB\Writable;
use App\Types\BoolHelper;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Utils\AppMode;
use App\Utils\QMAPIValidator;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
use App\Variables\QMVariableCategory;
use App\Variables\VariableSearchResult;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
abstract class GetVariableRequest extends Request {
	protected $requestParams;
	public $deletedAt;
	public $exactMatch;
	public $excludeVariableCategoryId;
	public $fallbackToAggregatedCorrelations;
	public $variableId;
	public $includePublic;
	public $includeTags;
	public $limit;
	public $manualTracking;
	public $name;
	public $mostCommonConnectorId;
	public $numberOfCorrelationsAsCause;
	public $numberOfCorrelationsAsEffect;
	public $offset;
	public $outcome;
	public $removeAdvancedProperties;
	public $searchPhrase;
	public $sort;
	public $status;
	public $statusNotUpdated;
	public $synonyms;
	public $unitCategoryName;
	public $upc;
	public $useWritableConnection;
	public $variableCategoryId;
	public $variableName;
	private $unitAbbreviatedName;
	private $unitId;
	private $connectorNameIsNotIn;
	private $variableCategory;
	/**
	 * GetVariableRequest constructor.
	 * @param QMSlim|null $app
	 * @param array $params
	 * @internal param int $userId
	 */
	public function __construct(QMSlim $app = null, array $params = []){
		if(isset($params['id'])){
			$this->setVariableId($params['id']);
		}
		if($app){
			$this->populate($app);
		}
		$this->requestParams = $params;
		$this->setLimit($params);
		$this->setOffset($params);
		$this->setName($params);
		$this->validateName();
		$this->convertNameToSearchPhraseIfNecessary();
	}
	/**
	 * @param QMSlim $app
	 */
	public function populate(QMSlim $app){
		$this->setApplication($app);
		$this->setVariableName($app->router()->getCurrentRoute()->getParam('variableName'));
	}
	/**
	 * @param $userRows
	 * @return array
	 */
	protected static function filterRowsByCategoryIfNecessary($userRows): array{
		if(QMRequest::getQMVariableCategory()){
			$filtered =
				QMArr::getElementsWithPropertyMatching('variableCategoryId', QMRequest::getQMVariableCategory()->id,
					$userRows);
			if(count($filtered)){
				$userRows = $filtered;
			}
		}
		return $userRows;
	}
	/**
	 * @param string $searchPhrase
	 * @param $userQb
	 */
	protected static function addCategoryClauseIfNecessary(string $searchPhrase, QMQB $userQb): void{
		if(QMRequest::getQMVariableCategory() && strlen($searchPhrase) < 3){
			$userQb->where(Variable::TABLE . '.' . Variable::FIELD_VARIABLE_CATEGORY_ID,
				QMRequest::getQMVariableCategory()->id);
		}
	}
	/**
	 * @return string
	 */
	public function getStatus(): ?string{
		return $this->status;
	}
	/**
	 * @param string $status
	 */
	public function setStatus($status){
		$this->status = $status;
	}
	/**
	 * @return string
	 */
	public function getUpc(): ?string{
		return $this->upc;
	}
	/**
	 * @return QMVariableCategory
	 */
	public function getQMVariableCategory(): ?QMVariableCategory{
		if(!isset($this->variableCategory)){
			$this->variableCategory = null;
		}
		return $this->variableCategory;
	}
	/**
	 * @param $connectorName
	 */
	public function setConnectorNameIsNotIn($connectorName){
		$this->connectorNameIsNotIn = $connectorName;
	}
	/**
	 * @param mixed $deletedAt
	 */
	public function setDeletedAt($deletedAt){
		$this->deletedAt = $deletedAt;
	}
	/**
	 * @return mixed
	 */
	public function getConnectorNameIsNotIn(){
		return $this->connectorNameIsNotIn;
	}
	/**
	 * @param array $params
	 */
	protected function validateRequestParams(array $params){
		if(isset($params[QMRequest::PARAM_INCLUDE_CHARTS]) && (!isset($params['name']) && !isset($params['id'])) &&
			!QMRequest::urlContains('/study')){
			le("Please specify name or id if requesting charts");
		}
        if(isset($params['category'])){
            $params['variableCategoryName'] = $params['category'];
            unset($params['category']);
        }
		QMAPIValidator::validateParams(self::getAllowedQueryParameters(), array_keys($params), 'variables/search_get');
	}
	/**
	 * @return array
	 */
	protected static function getAllowedQueryParameters(): array{
		return [
			'childUserTagVariableId',
			'commonAlias',
			'commonOnly',
			'concise',
			'createdAt',
			'unitCategoryName',
			'defaultUnitId',
			'doNotCreateNewCommonVariables',
			'earliestTaggedMeasurementTime',
			'effectOrCause',
			'exactMatch',
			'excludeLocal',
			'excludeVariableCategoryId',
			'fallbackToAggregatedCorrelations',
			'id',
			QMRequest::PARAM_INCLUDE_CHARTS,
			'includePrivate',
			'includePublic',
			'includeTags',
			'ingredientOfUserTagVariableId',
			'ingredientUserTagVariableId',
			'joinVariableId',
			'lastUpdated',
			'latestTaggedMeasurementTime',
			'manualTracking',
			'mostCommonConnectorId',
			'name',
			'numberOfCorrelationsAsCause',
			'numberOfCorrelationsAsEffect',
			'numberOfChanges',
			'numberOfMeasurements',
			'numberOfProcessedDailyMeasurements',
			'numberOfRawMeasurements',
			'numberOfUniqueDailyValues',
			'numberOfUserVariables',
			Variable::FIELD_OUTCOME,
			'parentUserTagVariableId',
			'publicEffectOrCause',
			'refresh',
			'removeAdvancedProperties',
			'searchPhrase',
			'search',
			'q',
			'shareUserMeasurements',
			'isPublic',
			'sourceName',
			'status',
			'statusNotUpdated',
			Variable::FIELD_SYNONYMS,
			'updatedAt',
			'update',
			'unitAbbreviatedName',
			'userId',
			'userOnly',
			'userTaggedVariableId',
			'userTagVariableId',
			'variableCategoryId',
			'variableCategoryName',
			'variableId',
			'variableName',
			'upc',
		];
	}
	/**
	 * @return array
	 */
	protected static function getTagQueryParameters(): array{
		return [
			'childUserTagVariableId',
			'ingredientOfUserTagVariableId',
			'ingredientUserTagVariableId',
			'joinVariableId',
			'parentUserTagVariableId',
			'userTaggedVariableId',
			'userTagVariableId',
		];
	}
	/**
	 * @return bool
	 */
	public static function requestIsSimple(): bool{
		foreach(self::getTagQueryParameters() as $parameter){
			if(QMRequest::getParam($parameter)){
				return false;
			}
		}
		return !empty(QMRequest::getSearchPhrase());
	}
	/**
	 * @return string
	 */
	public function getVariableName(): ?string{
		return $this->variableName;
	}
	/**
	 * @param string $variableName
	 */
	private function setVariableName($variableName){
		$this->variableName = $variableName;
	}
	/**
	 * @return bool
	 */
	public function isExactMatch(): ?bool{
		return $this->exactMatch;
	}
	/**
	 * @return int
	 */
	public function getVariableCategoryId(): ?int{
		if($this->getQMVariableCategory()){
			$this->variableCategoryId = $this->getQMVariableCategory()->id;
		}
		return $this->variableCategoryId = BaseVariableCategoryIdProperty::pluckOrDefault($this);
	}
	/**
	 * @param int|null $variableCategoryId
	 * @return int
	 */
	public function setVariableCategoryId(?int $variableCategoryId): ?int{
		$this->variableCategoryId = $variableCategoryId;
		return $this->variableCategoryId;
	}
	/**
	 * @param bool $exactMatch
	 */
	public function setExactMatch($exactMatch){
		$this->exactMatch = $exactMatch;
	}
	/**
	 * @return bool
	 */
	public function useWritableConnection(): ?bool{
		return $this->useWritableConnection;
	}
	/**
	 * @param bool $useWritableConnection
	 */
	public function setUseWritableConnection($useWritableConnection){
		$this->useWritableConnection = $useWritableConnection;
	}
	/**
	 * @return bool
	 */
	public function getOutcome(): ?bool{
		return $this->outcome;
	}
	/**
	 * @param mixed $outcome
	 */
	public function setOutcome($outcome){
		$this->outcome = $outcome;
	}
	/**
	 * @return int
	 */
	public function getLimit(): ?int{
		if(AppMode::isApiRequest() && $this->limit === null){
			$this->limit = 100;
		}
		return $this->limit;
	}
	/**
	 * @param int|array $limit
	 */
	public function setLimit($limit): void{
		if(is_array($limit)){
			$limit = $limit['limit'] ?? null;
		}
		if($limit === null){
			return;
		}
		if(!is_numeric($limit)){
			throw new BadRequestException("$limit is not a valid limit option.  Please use an integer between 1 and 100. ");
		}
		if(AppMode::isApiRequest()){
			if($limit === 0 || $limit > LimitParam::MAX_LIMIT){
				throw new BadRequestException("$limit is not a valid limit option.  Please use an integer between 1 and 100. ");
			}
		}
		$this->limit = $limit;
	}
	/**
	 * @return array
	 */
	protected function getSharedRequestParameterMap(): array{
		return [
			'commonAlias' => Variable::TABLE . '.common_alias',
			'defaultUnitId' => Variable::TABLE . '.default_unit_id',
			'name' => Variable::TABLE . '.name',
			VariableCategory::FIELD_OUTCOME => VariableCategory::TABLE . '.' . VariableCategory::FIELD_OUTCOME,
			//'variableCategoryId' => CommonVariable::TABLE.'.variable_category_id',
			'variableName' => Variable::TABLE . '.name',
		];
	}
	/**
	 * @return int
	 */
	public function getOffset(): int{
		return $this->offset ?: $this->setOffset();
	}
	/**
	 * @param array $params
	 * @return int
	 */
	public function setOffset(array $params = []): int{
		$this->offset = 0;
		if(isset($params['offset'])){
			$this->offset = (int)$params['offset'];
		}
		return $this->offset;
	}
	/**
	 * @return array
	 */
	public function getRequestParams(): array{
		$params = json_decode(json_encode($this), true);
		if(!isset($params)){
			le("Could not decode this request to get params!");
		}
		$unsetIfNull = [
			'id',
			'variableId',
			'name',
			Variable::FIELD_OUTCOME,
			'mostCommonConnectorId',
			'shareUserMeasurements',
			'isPublic',
			'status',
			'numberOfCorrelationsAsCause',
			'numberOfCorrelationsAsEffect',
		];
		foreach($unsetIfNull as $parameter){
			if(!array_key_exists($parameter, $params)){
				continue;
				//throw new \LogicException("$parameter property does not exist!");
			}
			if($params[$parameter] === null){
				unset($params[$parameter]);
			}
		}
		$unsetIfFalse = [
			Variable::FIELD_OUTCOME,
		];
		foreach($unsetIfFalse as $parameter){
			if(!array_key_exists($parameter, $params)){
				continue;
				//throw new \LogicException("$parameter property does not exist!");
			}
			if(!$params[$parameter]){
				unset($params[$parameter]);
			}
		}
		if($sort = $params['sort'] ?? null){
			$params['sort'] = str_replace('MeasurementTime', 'MeasurementStartAt', $sort);
		}
		return $params;
	}
	/**
	 * @return bool
	 */
	public function isIncludeTags(): ?bool{
		return $this->includeTags;
	}
	/**
	 * @param bool $includeTags
	 */
	public function setIncludeTags($includeTags){
		$this->includeTags = $includeTags;
	}
	/**
	 * @return Connection
	 */
	public function getDbConnection(): Connection{
		if($this->useWritableConnection()){
			return Writable::db();
		}
		return ReadonlyDB::db();
	}
	/**
	 * @return bool
	 */
	public function getExcludeVariableCategoryId(){
		return (int)$this->excludeVariableCategoryId;
	}
	/**
	 * @param bool $excludeVariableCategoryId
	 */
	public function setExcludeVariableCategoryId($excludeVariableCategoryId){
		$this->excludeVariableCategoryId = $excludeVariableCategoryId;
	}
	/**
	 * @return bool
	 */
	public function getSynonyms(): ?bool{
		return $this->synonyms;
	}
	/**
	 * @param bool $synonyms
	 */
	public function setSynonyms($synonyms){
		$this->searchPhrase = null;
		$this->name = null;
		$this->synonyms = $synonyms;
	}
	protected function replaceNameWithSearchPhraseIfGreaterThan125Characters(){
		if($this->getNameAttribute()){
			$name = $this->getNameAttribute();
			if(strlen($name) > 125){
				$this->setSearchPhrase(substr($name, 0, 120));
			}
		}
	}
	/**
	 * @return string
	 */
	public function getNameAttribute(): ?string{
		if(isset($this->requestParams['name'])){
			$this->name = $this->requestParams['name'];
		}
		return $this->name;
	}
	/**
	 * @param array|null $variables
	 * @return bool
	 */
	public function gotExactMatchIncludingSynonyms(array $variables = null): bool{
		if($this->getNameOrSearchPhrase()){
			if($variables == null){
				$variables = $this->getVariables();
			}
			foreach($variables as $variable){
				if($variable->name === $this->getNameOrSearchPhrase()){
					return true;
				}
				$synonyms = $variable->getSynonymsAttribute();
				if($synonyms && in_array($this->getNameOrSearchPhrase(), $synonyms, true)){
					return true;
				}
			}
		}
		return false;
	}
	/**
	 * @return string
	 */
	protected function getNameOrSearchPhrase(): ?string{
		return $this->getNameAttribute() ?: $this->getSearchPhrase();
	}
	/**
	 * @param string|array $name
	 */
	public function setName($name){
		if(is_array($name)){
			$name = $name['name'] ?? $name['variableName'] ?? null;
		}
		if(!$name){
			return;
		}
		$this->name = $name;
		$this->validateName();
		$this->convertNameToSearchPhraseIfNecessary();
	}
	private function validateName(){
		if($this->name !== null && !is_string($this->name)){
			throw new QMException(400, "name parameter must be a string");
		}
	}
	private function convertNameToSearchPhraseIfNecessary(){
		if($this->name &&
			substr($this->name, 0, 1) === "%"){ // Only check first character because we don't want to match (%RDA)
			$this->setSearchPhrase(str_replace("%", "", $this->name));
			$this->name = null;
		}
	}
	/**
	 * @return string
	 */
	public function getSearchPhrase(): ?string{
		$phrase = $this->searchPhrase;
		if(!$phrase){
			return null;
		}
        $phrase = str_replace("*", "%", $phrase);
		$phrase = QMStr::replaceDoubleParenthesis($phrase);
        $phrase = str_replace("+", " ", $phrase);
		if(strlen($phrase) > 125){
			$phrase = substr($phrase, 0, 124);
		}
        $this->setSearchPhrase($phrase);
		return $this->searchPhrase;
	}
	/**
	 * @param string $searchPhrase
	 */
	public function setSearchPhrase(string $searchPhrase){
		$this->name = $this->requestParams['name'] = null;
		$this->searchPhrase = $searchPhrase;
	}
	/**
	 * @return bool
	 */
	public function getStatusNotUpdated(): ?bool{
		return $this->statusNotUpdated;
	}
	/**
	 * @return bool
	 */
	public function isIncludePublic(): ?bool{
		return $this->includePublic;
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
	public function getUnitId(): string{
		if($this->unitId){
			return $this->unitId;
		}
		if($this->unitAbbreviatedName){
			return QMUnit::getByNameOrId($this->unitAbbreviatedName)->id;
		}
		return $this->unitCategoryName;
	}
	/**
	 * @param string $unitCategoryName
	 */
	public function setUnitCategoryName(string $unitCategoryName){
		$this->unitCategoryName = $unitCategoryName;
	}
	/**
	 * @return bool
	 */
	public function getExactMatch(): ?bool{
		if(!$this->getNameAttribute() && !$this->getSearchPhrase()){
			return false;
		}
		return $this->exactMatch;
	}
	/**
	 * @return bool
	 */
	public function getManualTracking(): bool{
		return BoolHelper::isTruthy($this->manualTracking);
	}
	/**
	 * @param bool $manualTracking
	 */
	public function setManualTracking($manualTracking){
		$this->manualTracking = $manualTracking;
	}
	/**
	 * @return int
	 */
	public function getVariableId(): ?int{
		return $this->variableId;
	}
	/**
	 * @param int $variableId
	 */
	public function setVariableId($variableId){
		$this->variableId = $variableId;
	}
	/**
	 * @return bool
	 */
	public function getRemoveAdvancedProperties(): bool{
		return (bool)$this->removeAdvancedProperties;
	}
	/**
	 * @return string
	 * i.e. (gt)1
	 */
	public function getNumberOfCorrelationsAsEffect(): ?string{
		return $this->numberOfCorrelationsAsEffect;
	}
	/**
	 * @param int $numberOfCorrelationsAsEffect
	 */
	public function setNumberOfCorrelationsAsEffect($numberOfCorrelationsAsEffect){
		$this->numberOfCorrelationsAsEffect = $numberOfCorrelationsAsEffect;
	}
	/**
	 * @return string
	 * i.e. (gt)1
	 */
	public function getNumberOfCorrelationsAsCause(): ?string{
		return $this->numberOfCorrelationsAsCause;
	}
	/**
	 * @param Builder $qb
	 */
	protected function applyManualTrackingFiltersIfNecessary(Builder $qb){
		if($this->getManualTracking()){
			$qb->where(VariableCategory::TABLE . '.manual_tracking', 1); //Exclude apps and websites
			$qb->where(Variable::TABLE . '.' . Variable::FIELD_DEFAULT_UNIT_ID, '<>',
				49); // Exclude dollar units
		}
	}
	/**
	 * @param Builder $qb
	 */
	protected function addSharedVariableClausesToQb(Builder $qb){
		if($this->getOffset()){
			$qb->offset($this->getOffset());
		}
		if($this->getLimit()){
			$qb->limit($this->getLimit());
		}
		$this->applyManualTrackingFiltersIfNecessary($qb);
		//if($this->getName()){$qb->where(CommonVariable::TABLE.'.name', $this->getName());}
		$this->addSearchPhraseFilter($qb);
		$this->addSynonymFilter($qb);  // This is done as a fallback
		$this->addVariableCategoryWhereClause($qb);
		$this->addUpcWhereClause($qb);
		//if($this->getId()){$qb->where(CommonVariable::TABLE.'.id', $this->getId());} // I think this is already done in  QueryBuilderHelper::applyFilterParamsIfExist
		$this->setConnectorNotInWhereClause($qb);
		$qb->whereNull(Variable::TABLE . '.' . Variable::FIELD_DELETED_AT);
	}
	/**
	 * @param Builder $qb
	 */
	protected function setConnectorNotInWhereClause(Builder $qb){
		if($toAvoid = $this->getConnectorNameIsNotIn()){
			foreach($toAvoid as $connectorName){
				$notIn[] = QMConnector::getDataSourceWithoutDBQuery($connectorName)->id;
			}
		}
		if(isset($notIn) && count($notIn)){
			$qb->whereNotIn(Variable::TABLE . '.' . Variable::FIELD_MOST_COMMON_CONNECTOR_ID, $notIn);
		}
	}
	/**
	 * @param Builder $qb
	 */
	protected function addSearchPhraseFilter(Builder $qb){
		if($q = $this->getSearchPhrase()){
			if($this->getExactMatch()){
				$qb->where(Variable::TABLE . '.name', str_replace('%', '', $q));
				$this->setName(null);
			} else{
				self::addWhereClauseForEachWord($q, $qb);
			}
		}
	}
	/**
	 * @param string $searchPhrase
	 * @param Builder $qb
	 * @return void
	 */
	protected static function addWhereClauseForEachWord(string $searchPhrase, Builder $qb){
		$searchPhrase = QMStr::replaceDoubleParenthesis($searchPhrase);
		$searchPhrase = str_replace('%', '', $searchPhrase);
		$words = preg_split('/\s+/', $searchPhrase);
		foreach($words as $word){
			$qb->where(Variable::TABLE . '.name', \App\Storage\DB\ReadonlyDB::like(), '%' . $word . '%');
		}
	}
	/**
	 * @param Builder $qb
	 */
	public function addSynonymFilter(Builder $qb){
		if($this->getSynonyms()){
			if($this->getNameAttribute() || $this->getSearchPhrase()){
				QMLog::error("We should not have name or search phrase if using synonyms!");
			}
			$qb->where(Variable::TABLE . '.synonyms', \App\Storage\DB\ReadonlyDB::like(), '%' . $this->getSynonyms() . '%');
		}
	}
	/**
	 * @param Builder $qb
	 */
	private function addVariableCategoryWhereClause(Builder $qb){
		$id = $this->getVariableCategoryId();
		if($id){
			if(stripos($id, '(ne)') !== false){
				$id = str_replace('(ne)', '', $id);
				$qb->where(Variable::TABLE . '.variable_category_id', '<>', $id);
			} else{
				$qb->where(Variable::TABLE . '.variable_category_id', $id);
			}
		}
	}
	/**
	 * @param Builder $qb
	 */
	private function addUpcWhereClause(Builder $qb){
		if($this->getUpc()){
			$qb->where(Variable::TABLE . '.' . Variable::FIELD_UPC_14, $this->getUpc());
		}
	}
	/**
	 * @return QMCommonVariable[]|QMUserVariable[]
	 */
	abstract public function getVariables(): array;
	/**
	 * @return QMCommonVariable[]|QMUserVariable[]
	 */
	public function getVariablesWithMatchingSynonyms(): array{
		$this->setSynonyms($this->getSearchPhrase());
		return $this->getVariables();
	}
	/**
	 * @param string|null $sortBy
	 * @return bool
	 */
	private static function sortByCorrelations(string $sortBy = null): bool{
		$sortByCorrelations = strpos(strtolower($sortBy), 'user_variable_relationships') !== false;
		return $sortByCorrelations;
	}
	/**
	 * @param string|null $sortBy
	 * @return bool
	 */
	private static function sortByUserVariables(string $sortBy = null): bool{
		return strpos(strtolower($sortBy), 'uservariables') !== false;
	}
	/**
	 * @param string|null $sortBy
	 * @return bool
	 */
	private static function sortByMeasurements(string $sortBy = null): bool{
		return strpos(strtolower($sortBy), 'measurements') !== false;
	}
	/**
	 * @param VariableSearchResult $variable
	 * @param string $sortBy
	 */
	public static function setSubtitle(VariableSearchResult $variable, string $sortBy){
		if($variable->numberOfCorrelations && self::sortByCorrelations($sortBy)){
			$variable->setStudiesSubtitle();
		}
		if($variable->numberOfUserVariables && self::sortByUserVariables($sortBy)){
			$variable->setUserVariablesSubtitle();
		}
		if($variable->getNumberOfMeasurements() && self::sortByMeasurements($sortBy)){
			$variable->setMeasurementsSubtitle();
		}
		if(!$variable->subtitle){
			if($variable->numberOfUserVariables){
				$variable->setUserVariablesSubtitle();
			}
			if($variable->numberOfCorrelations){
				$variable->setStudiesSubtitle();
			}
			if(isset($variable->userId) && $variable->getNumberOfMeasurements()){
				$variable->setMeasurementsSubtitle();
			}
		}
	}
	/**
	 * @param string|null $searchPhrase
	 * @param QMUser|null $user
	 * @return QMCommonVariable[]|QMUserVariable[]
	 */
	public static function getVariablesSimple(string $searchPhrase = null, QMUser $user = null): ?array{
		if(!$user){
			$user = QMAuth::getQMUser();
		}
		if(!$searchPhrase){
			$searchPhrase = QMRequest::getSearchPhrase();
		}
		$userVariables = [];
		if($user){
			$userVariables = GetUserVariableRequest::getUserVariablesSimple($searchPhrase, $user);
		}
		$includePublic = QMRequest::getParam('includePublic');
		$limit = QMVariable::getLimitFromRequestOrModelDefault();
		if($includePublic !== false && count($userVariables) < $limit){
			$commonVariables = GetCommonVariablesRequest::getCommonVariablesSimple($searchPhrase, $userVariables);
			QMArr::sortDescending($commonVariables, 'manualTracking'); // May be overridden by request sort later
			$variables = QMArr::arrayMergeUnique('variableId', $userVariables, $commonVariables);
		} else{
			$variables = $userVariables;
		}
		if(!$variables){
			return $variables;
		}
		$sort = SortParam::getSort();
		if(!empty($sort)){
			$variables = QMArr::sortByProperty($variables, $sort);
			self::addSubTitles($variables, $sort);
		}
		$variables = QMVariable::putExactMatchFirst($variables, $searchPhrase);
		return $variables;
	}
	/**
	 * @param VariableSearchResult[]|QMUserVariable[] $variables
	 * @param string $sortBy
	 */
	public static function addSubTitles(array $variables, string $sortBy): void{
		foreach($variables as $v){
			self::setSubtitle($v, $sortBy);
		}
	}
}
