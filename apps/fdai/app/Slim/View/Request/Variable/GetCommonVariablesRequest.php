<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\View\Request\Variable;
use App\Http\Parameters\SortParam;
use App\Logging\QMLog;
use App\Models\Variable;
use App\Models\VariableCategory;
use App\Slim\View\Request\QMRequest;
use App\Storage\DB\QMQB;
use App\Storage\DB\ReadonlyDB;
use App\Storage\QueryBuilderHelper;
use App\Types\QMArr;
use App\Variables\QMCommonVariable;
use App\Variables\QMVariable;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
/** Class GetCommonVariablesRequest
 * @package App\Slim\View\Request\Variable
 */
class GetCommonVariablesRequest extends GetVariableRequest {
	public const includePrivate = 'includePrivate';
	private static $columnsArray;
	public $includePrivate;
	public $effectOrCause;
	public $publicEffectOrCause;
	public $numberOfAggregatedCorrelationsAsCause;
	public $numberOfAggregatedCorrelationsAsEffect;
	private $commonVariables;
	public static $aliasToFieldNameMap = [
		'createdAt' => Variable::TABLE . '.created_at',
		'defaultUnitId' => Variable::TABLE . '.default_unit_id',
		'unitId' => Variable::TABLE . '.default_unit_id',
		//'deletedAt' => CommonVariable::TABLE.'.deleted_at',
		'id' => Variable::TABLE . '.id',
		'lastUpdated' => Variable::TABLE . '.updated_at',
		'name' => Variable::TABLE . '.name',
		'numberOfCorrelationsAsCause' => Variable::TABLE . '.number_of_global_variable_relationships_as_cause',
		'numberOfGlobalVariableRelationshipsAsCause' => Variable::TABLE .
			'.number_of_global_variable_relationships_as_cause',
		'numberOfCorrelationsAsEffect' => Variable::TABLE . '.number_of_global_variable_relationships_as_effect',
		'numberOfGlobalVariableRelationshipsAsEffect' => Variable::TABLE .
			'.number_of_global_variable_relationships_as_effect',
		'numberOfMeasurements' => Variable::TABLE . '.number_of_measurements',
		'numberOfRawMeasurements' => Variable::TABLE . '.number_of_measurements',
		'numberOfUserVariables' => Variable::TABLE . '.number_of_user_variables',
		Variable::FIELD_OUTCOME => VariableCategory::TABLE . '.outcome',
		'updatedAt' => Variable::TABLE . '.updated_at',
		'updatedTime' => Variable::TABLE . '.updated_at',
		//'variableCategoryId' => CommonVariable::TABLE.'.variable_category_id'
	];
	/**
	 * GetCommonVariablesRequest constructor.
	 * @param array|GetUserVariableRequest $requestParams
	 */
	public function __construct($requestParams = null){
		if($requestParams){
			$this->setRequestParams($requestParams);
			parent::__construct(null, $this->requestParams);
		}
	}
	/**
	 * @param string $searchPhrase
	 * @return QMQB
	 */
	public static function getCommonVariableSearchQB(string $searchPhrase): Builder{
		$commonQb = self::getBaseQB();
		$sortField = SortParam::getSort();
		if($sortField){
			$sortField = str_replace('latestTaggedMeasurementTime', 'numberOfUserVariables', $sortField);
			$sortField = str_replace('latestMeasurementTime', 'numberOfUserVariables', $sortField);
		}
		QMCommonVariable::addLimitOffsetSort($commonQb, Variable::TABLE, $sortField);
		GetVariableRequest::addWhereClauseForEachWord($searchPhrase, $commonQb);
		if(QMRequest::getQMVariableCategory() && strlen($searchPhrase) < 3){
			$commonQb->where(Variable::TABLE . '.' . Variable::FIELD_VARIABLE_CATEGORY_ID,
				QMRequest::getQMVariableCategory()->id);
		}
		return $commonQb;
	}
	/**
	 * @param string $searchPhrase
	 * @param $userVariables
	 * @return array|QMCommonVariable[]
	 */
	public static function getCommonVariablesSimple(string $searchPhrase, $userVariables): array{
		$qb = self::getCommonVariableSearchQB($searchPhrase)->where(Variable::TABLE . '.' .
			Variable::FIELD_IS_PUBLIC, 1);
		QueryBuilderHelper::applyFilterParamsIfExist($qb, self::$aliasToFieldNameMap, $_GET);
		$commonRows = $qb->getArray();
		if(empty($commonRows) && empty($userVariables)){
			$qb = self::getCommonVariableSearchQB($searchPhrase);
			$commonRows = $qb->getArray();
		}
		$userVariableVariableIds = QMArr::getAllValuesForKeyOrProperty($userVariables, 'variableId');
		$commonRows = QMArr::getWithPropertyNotInArray($commonRows, 'variableId', $userVariableVariableIds);
		$commonRows = GetVariableRequest::filterRowsByCategoryIfNecessary($commonRows);
		$commonVariables = QMCommonVariable::convertRowsToVariables($commonRows);
		QMArr::sortDescending($commonVariables, 'numberOfUserVariables'); // May be overridden by request sort later
		$commonVariables = self::getExactMatchIfNecessary($searchPhrase, $commonVariables);
		return $commonVariables;
	}
	/**
	 * @param string $searchPhrase
	 * @param $variables
	 * @return array|QMCommonVariable[]|QMVariable[]
	 */
	private static function getExactMatchIfNecessary(string $searchPhrase, $variables): array{
		$exactMatch = QMVariable::getExactMatchFromArray($variables, $searchPhrase);
		if($exactMatch){
			$variables = QMVariable::putExactMatchFirst($variables, $searchPhrase);
		} else{
			if(count($variables) < QMCommonVariable::getLimitFromRequestOrModelDefault() - 1){
				return $variables;
			}
			$exactMatch = QMCommonVariable::findByName($searchPhrase);
			if($exactMatch){
				$variables = array_merge([$exactMatch], $variables);
			}
		}
		return $variables;
	}
	/**
	 * @param string $sortField
	 * @return string
	 */
	public function setSort($sortField = null): string{
		if($sortField){
			$this->sort = $sortField;
		}
		if(!$this->sort && $this->getNumberOfCorrelationsAsCause()){
			$this->sort = '-numberOfGlobalVariableRelationshipsAsCause';
			$this->outcome = false;
		}
		if(!$this->sort && $this->getNumberOfCorrelationsAsEffect()){
			$this->sort = '-numberOfGlobalVariableRelationshipsAsEffect';
			$this->outcome = false;
		}
		return $this->sort;
	}
	/**
	 * @param array $requestParams
	 */
	public function setRequestParams($requestParams){
		$requestParams =
			static::properlyFormatRequestParams($requestParams, QMCommonVariable::getLegacyRequestParameters());
		$this->validateRequestParams($requestParams);
		foreach($requestParams as $key => $value){
			$this->$key = $value;
			if($this->$key == ""){
				$this->$key = null;
			}
		}
		$this->requestParams = $requestParams;
	}
	/**
	 * @param bool $outcome
	 * @return bool
	 */
	public function setOutcome($outcome): bool{
		if($this->getNumberOfAggregatedCorrelationsAsEffect()){
			$this->outcome = false;
		}
		if($this->getNumberOfAggregatedCorrelationsAsCause()){
			$this->outcome = false;
		}
		if($outcome !== null){
			$this->outcome = (bool)$outcome;
		}
		return $this->outcome;
	}
	/**
	 * @return int
	 */
	public function getNumberOfAggregatedCorrelationsAsCause(): ?int{
		return $this->numberOfAggregatedCorrelationsAsCause;
	}
	/**
	 * @return int
	 */
	public function getNumberOfAggregatedCorrelationsAsEffect(): ?int{
		return $this->numberOfAggregatedCorrelationsAsEffect;
	}
	/**
	 * @return bool
	 */
	public function getIncludePrivate(): bool{
		if($this->includePrivate !== null){
			return $this->includePrivate;
		}
		if($this->getVariableId()){
			return true;
		}
		if($this->getNameAttribute()){
			return true;
		}
		if($this->getExactMatch()){
			return true;
		}
		return false;
	}
	/**
	 * @return string
	 */
	public function getEffectOrCause(): ?string{
		return $this->effectOrCause;
	}
	/**
	 * @return string
	 */
	public function getPublicEffectOrCause(): ?string{
		return $this->publicEffectOrCause;
	}
	/**
	 * @return string
	 */
	public function getSort(): ?string{
		return $this->sort;
	}
	/**
	 * @param Builder $qb
	 */
	private function addCommonVariableClausesToQb(Builder $qb){
		if(!$this->getIncludePrivate()){
			$qb->where(Variable::TABLE . '.is_public', '=', 1);
		}
		self::addCorrelationJoins($qb, $this->getEffectOrCause(), $this->getPublicEffectOrCause());
		$params = $this->getRequestParams();
		if($sort = $params['sort'] ?? null){
			$params['sort'] = str_replace('latestMeasurementStartAt', 'numberOfUserVariables', $sort);
		}
		QueryBuilderHelper::applyOffsetLimitSort($qb, $params, self::$aliasToFieldNameMap);
		if(!$qb->orders){
			$qb->orderBy(Variable::TABLE . '.number_of_user_variables', 'DESC');
		}
		if($this->getStatus()){
			$qb->where(Variable::TABLE . '.' . Variable::FIELD_STATUS, $this->getStatus());
		}
		$this->replaceNameWithSearchPhraseIfGreaterThan125Characters();
		QueryBuilderHelper::applyFilterParamsIfExist($qb, self::$aliasToFieldNameMap, $this->getRequestParams());
	}
	/**
	 * @return QMCommonVariable[]
	 */
	public function getCommonVariables(): array{
		if($this->commonVariables !== null){
			return $this->commonVariables;
		}
		$qb = $this->qb = self::getBaseQB($this->getDbConnection());
		$this->addCommonVariableClausesToQb($qb);
		$this->addSharedVariableClausesToQb($qb);
		$ids = $qb->getIds();
		$variables = Variable::find($ids);
		if(!$qb->wheres){
			QMLog::error("No where clauses on variable query!");
		}
		if($this->limit && count($variables) > $this->limit){
			QMLog::exceptionIfNotProduction("limit is $this->limit but got " . $variables->count() . " rows from DB!");
		}
		$vars = Variable::toDBModels($variables);
		if($sort = $this->getSort()){
			self::addSubTitles($vars, $sort);
		}
		if(!count($vars) && $this->getSearchPhrase()){
			$vars = $this->getVariablesWithMatchingSynonyms();
		}
		if($this->getSearchPhrase()){
			$vars = QMVariable::putExactMatchFirst($vars, $this->getSearchPhrase());
		}
		return $this->commonVariables = $vars;
	}
	/**
	 * @param Builder $qb
	 * @param string|null $effectOrCause
	 * @param string|null $publicEffectOrCause
	 */
	private static function addCorrelationJoins(Builder $qb, string $effectOrCause = null,
		string $publicEffectOrCause = null){
		if($effectOrCause == 'cause'){
			$qb->join('user_variable_relationships AS c', Variable::TABLE . '.id', '=', 'c.cause_variable_id');
		}
		if($effectOrCause == 'effect'){
			$qb->join('user_variable_relationships AS c', Variable::TABLE . '.id', '=', 'c.effect_variable_id');
		}
		if($publicEffectOrCause == 'cause'){
			$qb->join('global_variable_relationships AS ac', Variable::TABLE . '.id', '=', 'ac.cause_variable_id');
		}
		if($publicEffectOrCause == 'effect'){
			$qb->join('global_variable_relationships AS ac', Variable::TABLE . '.id', '=', 'ac.effect_variable_id');
		}
	}
	/**
	 * @param Connection $db
	 * @return array
	 */
	public static function getCommonVariableColumnsArray($db): array{
		if(self::$columnsArray){
			return self::$columnsArray;
		}
		$arr = [
			Variable::TABLE . '.id',
			Variable::TABLE . '.id as variableId',
			Variable::TABLE . '.name',
			Variable::TABLE . '.' . Variable::FIELD_ADDITIONAL_META_DATA . ' as commonAdditionalMetaData',
			Variable::TABLE . '.' . Variable::FIELD_BEST_CAUSE_VARIABLE_ID . ' as commonBestCauseVariableId',
			Variable::TABLE . '.' . Variable::FIELD_BEST_EFFECT_VARIABLE_ID . ' as commonBestEffectVariableId',
			Variable::TABLE . '.' . Variable::FIELD_EARLIEST_NON_TAGGED_MEASUREMENT_START_AT .
			' as earliestNonTaggedMeasurementStartAt',
			Variable::TABLE . '.' . Variable::FIELD_EARLIEST_TAGGED_MEASUREMENT_START_AT .
			' as earliestTaggedMeasurementStartAt',
			Variable::TABLE . '.' . Variable::FIELD_LATEST_NON_TAGGED_MEASUREMENT_START_AT .
			' as latestNonTaggedMeasurementStartAt',
			Variable::TABLE . '.' . Variable::FIELD_LATEST_TAGGED_MEASUREMENT_START_AT .
			' as latestTaggedMeasurementStartAt',
			Variable::TABLE . '.' . Variable::FIELD_MAXIMUM_ALLOWED_VALUE . ' as commonMaximumAllowedValueInCommonUnit',
			Variable::TABLE . '.' . Variable::FIELD_MINIMUM_ALLOWED_VALUE . ' as commonMinimumAllowedValueInCommonUnit',
			Variable::TABLE . '.' . Variable::FIELD_OPTIMAL_VALUE_MESSAGE . ' as commonOptimalValueMessage',
			Variable::TABLE . '.default_unit_id as commonUnitId',
			Variable::TABLE . '.default_unit_id as unitId',
			VariableCategory::TABLE . '.' . VariableCategory::FIELD_OUTCOME . ' AS ' .
			VariableCategory::FIELD_OUTCOME,
			$db->raw('COALESCE(' . Variable::TABLE . '.' . Variable::FIELD_IS_PUBLIC . ', ' . VariableCategory::TABLE .
				'.' . VariableCategory::FIELD_IS_PUBLIC . ') as isPublic'),
			$db->raw('COALESCE(' . Variable::TABLE . '.onset_delay, ' . VariableCategory::TABLE .
				'.onset_delay) as onsetDelay'),
			$db->raw('COALESCE(' . Variable::TABLE . '.duration_of_action, ' . VariableCategory::TABLE .
				'.duration_of_action) as durationOfAction'),
		];
		$arr = QMCommonVariable::addSelectFields($arr);
		return self::$columnsArray = $arr;
	}
	/**
	 * @param Connection $db
	 * @return QMQB
	 */
	public static function getBaseQB($db = null): QMQB{
		if(!$db){
			$db = ReadonlyDB::db();
		}
		$qb = $db->table(Variable::TABLE)->select(self::getCommonVariableColumnsArray($db))
			->leftJoin(VariableCategory::TABLE, Variable::TABLE . '.variable_category_id', '=',
				VariableCategory::TABLE . '.id');
		return $qb;
	}
	/**
	 * @param mixed $includePrivate
	 */
	public function setIncludePrivate($includePrivate = true){
		$this->includePrivate = $includePrivate;
	}
	/**
	 * @return QMCommonVariable[]
	 */
	public function getVariables(): array{
		return $this->getCommonVariables();
	}
	/**
	 * @param string $searchPhrase
	 * @param bool $includePrivate
	 * @return null|QMCommonVariable[]
	 */
	public static function getWithNameContainingAllWords(string $searchPhrase, $includePrivate = true): array{
		$req = new self();
		$req->setSearchPhrase($searchPhrase);
		$req->setIncludePrivate($includePrivate);
		$results = $req->getCommonVariables();
		return $results;
	}
	/**
	 * @param string $string
	 * @return null|QMCommonVariable[]
	 */
	public static function getWithNameContainingExactString(string $string): array{
		$qb = self::getBaseQB();
		$qb->where(Variable::TABLE . '.' . Variable::FIELD_NAME, \App\Storage\DB\ReadonlyDB::like(), "%$string%");
		$rows = $qb->getArray();
		$variables = QMCommonVariable::instantiateNonDBRows($rows);
		return $variables;
	}
}
