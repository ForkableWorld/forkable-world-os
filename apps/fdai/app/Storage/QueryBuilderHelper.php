<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage;
use App\Exceptions\AccessTokenExpiredException;
use App\Exceptions\BadRequestException;
use App\Http\Parameters\LimitParam;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Models\UserVariableRelationship;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Properties\UserVariableRelationship\CorrelationStatusProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
use App\Storage\DB\QMQB;
use App\Storage\DB\Writable;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Utils\AppMode;
use App\Utils\Constraint;
use App\Variables\QMVariableCategory;
use Auth;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use SqlFormatter;
class QueryBuilderHelper {
	const OPERATOR_NOT_NULL = 'not null';
    const OPERATOR_IS_NULL = 'is null';
    const HAVE_AN_ERROR = "that have an error";
    public static array $GLOBAL_EXCLUDE_FROM_WHERE_PARAMS = [
        QMRequest::PARAM_ACCESS_TOKEN,
        QMRequest::PARAM_LIMIT,
        QMRequest::PARAM_OFFSET,
        QMRequest::PARAM_SORT,
        'fields',
        "_",
        QMRequest::PARAM_PROFILE,
        QMRequest::PARAM_CREATE_TEST,
    ];
    /**
     * @param Builder $qb
     * @param string $sortFieldName
     * @param array $aliasToFieldNameMap =
     * @throws BadRequestException
     */
    public static function setSort($qb, string $sortFieldName, array $aliasToFieldNameMap){
        if($qb instanceof \Illuminate\Database\Eloquent\Builder){
            $qb = $qb->getQuery();
        }
        $direction = 'asc';
        $qb->orders = [];
        $sortFieldName = str_replace("startTimeEpoch", "startTime", $sortFieldName);
        if($sortFieldName === "startTimeEpoch"){$sortFieldName = "startTime";}
        if($sortFieldName === "-startTimeEpoch"){$sortFieldName = "-startTime";}
        $sortFieldName = str_replace("latestMeasurementStartAt", "latestMeasurementTaggedMeasurementStartAt", $sortFieldName);
        if(strpos($sortFieldName, '-') === 0){
            $sortFieldName = substr($sortFieldName, 1);
            $direction = 'desc';
        }
        if(array_key_exists($sortFieldName, $aliasToFieldNameMap)){
            if(is_string($aliasToFieldNameMap[$sortFieldName])){
                $qb->orderBy($aliasToFieldNameMap[$sortFieldName], $direction);
            }else{
                $qb->orderBy($sortFieldName, $direction);
            }
        }else{
            throw new BadRequestException("Incorrect sort field '$sortFieldName'.  ".
                self::createAvailableFieldListString($aliasToFieldNameMap));
        }
    }
    /**
     * @param Builder|\Illuminate\Database\Eloquent\Builder $qb
     * @param $fieldString
     * @param array $aliasToFieldNameMap
     * @throws BadRequestException
     */
    public static function setFields($qb, $fieldString, array $aliasToFieldNameMap){
        if($qb instanceof \Illuminate\Database\Eloquent\Builder){
            $qb = $qb->getQuery();
        }
        $fields = explode(',', $fieldString);
        $qb->columns = [];
        foreach($fields as $field){
            if(isset($aliasToFieldNameMap[$field])){
                $qb->columns[] = $aliasToFieldNameMap[$field];
            }else{
                throw new BadRequestException("Incorrect field '$field'.  ".self::createAvailableFieldListString($aliasToFieldNameMap));
            }
        }
        if(!$qb->columns){
            throw new BadRequestException('Empty field list');
        }
    }
    /**
     * @param Builder|\Illuminate\Database\Eloquent\Builder $qb
     * @param array|null $params
     * @param array|null $aliasToFieldNameMap
     */
    public static function applyOffsetLimitSort($qb, array $params = null, array $aliasToFieldNameMap = null){
        if($qb instanceof \Illuminate\Database\Eloquent\Builder){$qb = $qb->getQuery();}
        if(!$params){$params = [];}
        $limit = $params[QMRequest::PARAM_LIMIT] ?? null;
        if($limit === null && AppMode::isApiRequest()){$limit = LimitParam::DEFAULT_LIMIT;}
        if($limit){
            $qb->take($limit);
            $qb->skip( $params['offset'] ?? 0);
        }
        $sort = $params['sort'] ?? null;
        $aliasToFieldNameMap = static::getAliasToFieldNameMap($qb, $aliasToFieldNameMap);
        if($sort){
            static::setSort($qb, $sort, $aliasToFieldNameMap);
        }
        if(isset($params['fields'])){
            static::setFields($qb, $params['fields'], $aliasToFieldNameMap);
        }
    }
    /**
     * @param Builder|\Illuminate\Database\Eloquent\Builder $qb
     * @param array $aliasToFieldNameMap
     * @param array $params
     */
    public static function applyFilterParamsIfExist($qb, array $aliasToFieldNameMap, array $params){
        $params = self::replaceNamesWithIds($params);
        foreach($aliasToFieldNameMap as $param => $column){
            if(array_key_exists($param, $params)){
                if(is_array($params[$param])){
                    foreach($params[$param] as $requestParameterValue){
                        self::applySingleFilterParam($qb, $column, $requestParameterValue);
                    }
                }else{
                    $val = $params[$param];
                    if($val === null){continue;}
                    self::applySingleFilterParam($qb, $column, $params[$param]);
                }
            }
        }
    }
    /**
     * @param Builder $qb
     * @param string $field
     * @param $value
     */
    public static function applySingleFilterParam($qb, string $field, $value){
        if($qb instanceof \Illuminate\Database\Eloquent\Builder){$qb = $qb->getQuery();}
        if(str_contains($field, '.')){
            $exploded = explode('.', $field);
            $table = $exploded[0];
            //$table = self::aliasToTable($table, $qb);
            $field = $exploded[1];
        } else {
            $table = $qb->from;
        }
        $class = BaseModel::getClassByTable($table);
        if(!class_exists($class)) {
            $ignore = ["ActionEvent"];
            foreach($ignore as $item){if(str_contains($class, $item)){return;}}
            QMLog::error("$class not found for query builder");
            return;
        }
        if(!$class::hasColumn($field)){
            $replaced = str_replace($table."_", "", $field);
            if($class::hasColumn($replaced)){
                $field = $replaced;
            }
        }
        //if(!Writable::columnExists($table, $field)){return;}
        if(!$class::hasColumn($field)){
            $ignore =
                [
                    "_field",
                    '_page',
                    'trashed',
                    'page',
                    'relationshipType',
                    '_filter',
                    'resourceId',
                    '[from]',
                    '_order',
                    '_direction',
                    'search',
                    'perPage',
                    'filters',
                    'orderBy',
                    'viaRelationship',
                    'viaResource',
                    'draw',
                    'length',
                    'start',
                    'email',
	                'logLevel'
                ];
            foreach($ignore as $item){if(str_contains($field, $item)){return;}}
            QMLog::error("No $field column in $table table.  Add to ignore list in ".
                "\App\Storage\QueryBuilderHelper::applySingleFilterParam to ignore this warning.");
            return;
        }
        $constraint = new Constraint($field, $table, $value);
        $constraint->apply($qb);
    }
    /**
     * @param Builder $qb
     * @param $aliasToFieldMap
     * @return array
     */
    public static function getAliasToFieldNameMap($qb, $aliasToFieldMap): array{
        if(!$aliasToFieldMap){$aliasToFieldMap = [];}
        $columns = $qb->columns ?? [];
        if(!$columns){
            $columns = BaseModel::getColumnsForTable($qb->from);
            foreach($columns as $column){$aliasToFieldMap[$column] = $column;}
            return $aliasToFieldMap;
        }
        foreach($columns as $fieldStringOrExpression){
            $alias = $fieldStringOrExpression;
            if($fieldStringOrExpression instanceof Expression){
                $selectStatement = $fieldStringOrExpression->getValue();
            }else{
                $selectStatement = $fieldStringOrExpression;
            }
            if($pos = stripos($selectStatement, ' AS ')){
                $alias = substr($selectStatement, $pos + strlen(' AS '));
            }
            if($fieldStringOrExpression instanceof Expression){
		if(!is_string($alias)){le('!is_string($alias)');}
                $aliasToFieldMap[$alias] = $fieldStringOrExpression;
            }else{
                $aliasToFieldMap[$alias] = substr($fieldStringOrExpression, 0, $pos);
            }
        }
        return $aliasToFieldMap;
    }
    /**
     * @param array $availableFields
     * @return string
     */
    private static function createAvailableFieldListString(array $availableFields): string
    {
        $availableFieldsList = array_keys($availableFields);
        sort($availableFieldsList);
        $availableFieldsList = implode("\n\t-", $availableFieldsList);
        $availableFieldsListString = "Available fields are:\n\t-".$availableFieldsList.
            "\n\tSee https://app.quantimo.do/account/api-explorer or go to https://help.quantimo.do for more info.";
        return $availableFieldsListString;
    }
    /**
     * @param array $requestParams
     * @return array
     */
    public static function replaceNamesWithIds(array $requestParams): array {
        if (isset($requestParams['causeVariableCategoryName'])) {
            $requestParams['causeVariableCategoryId'] =
                QMVariableCategory::find($requestParams['causeVariableCategoryName'])->getId();
            unset($requestParams['causeVariableCategoryName']);
        }
        if (isset($requestParams['effectVariableCategoryName'])) {
            $requestParams['effectVariableCategoryId'] =
                QMVariableCategory::find($requestParams['effectVariableCategoryName'])->getId();
            unset($requestParams['effectVariableCategoryName']);
        }
        return $requestParams;
    }

    /**
     * @param $qb
     * @param array $filters
     */
    public static function applyFilters($qb, array $filters){
        foreach($filters as $parameter => $value){
            if(is_array($value)){continue;}
            $isGlobal = QMArr::inArrayCaseInsensitive($parameter, self::$GLOBAL_EXCLUDE_FROM_WHERE_PARAMS);
            if($isGlobal){continue;}
            self::applySingleFilterParam($qb, $parameter, $value);
        }
    }
    /**
     * @param \Illuminate\Database\Eloquent\Builder $qb
     * @param string $default
     * @param string $dir
     */
    public static function addOrderByFromRequestIfNecessary($qb, string $default, string $dir = 'desc'){
        $field = $default;
        if(isset($_GET["order"][0])){
            $orderFromRequest = $_GET["order"][0];
            $orderColumnNumberFromReq = (int)$orderFromRequest["column"];
            $dir = $orderFromRequest["dir"];
            /** @var BaseModel $m */
            $m = $qb->getModel();
            $all = $m->getColumns();
            $tableColumns = $_GET["columns"];
            $orderTableColumn = $tableColumns[$orderColumnNumberFromReq];
            $orderable = $orderTableColumn["orderable"] ?? null;
            $fieldNameForOrderColumn = $orderTableColumn["data"];
			if(!in_array($fieldNameForOrderColumn, $all)){
				$orderTableColumn = $tableColumns[$orderColumnNumberFromReq - 1];
				$orderable = $orderTableColumn["orderable"] ?? null;
				$fieldNameForOrderColumn = $orderTableColumn["data"];
			}
            if(in_array($fieldNameForOrderColumn, $all)){
                $field = $fieldNameForOrderColumn;
            } else{
                QMLog::error("Could not find field $fieldNameForOrderColumn to sort by");
				return;
            }
        }
        if(stripos($field, '.') === false){
            $field = $qb->getModel()->getTable().'.'.$field;
        }
        $qb->orderBy($field, $dir);
    }
    /**
     * @param \Illuminate\Database\Eloquent\Builder $qb
     */
    public static function addFilterParamsFromReferrerIfNecessary($qb){
        if(QMRequest::getParamFromReferrer(QMRequest::PARAM_ERRORED)){
            self::addErroredClauses($qb);
            return;
        }
        $model = $qb->getModel();
        $fields = $model->getColumns();
        foreach($fields as $field){
            $requestParam = QMRequest::getParamFromReferrer($field);
            if($requestParam !== null){
                if(strtolower($requestParam) === self::OPERATOR_NOT_NULL){
                    $qb->whereNotNull($field);
                } elseif(strtolower($requestParam) === 'null') {
                    $qb->whereNull($field);
                } else{
                    $qb->where($model->getTable().'.'.$field, $requestParam);
                }
            }
        }
        if(QMRequest::getParamFromReferrer(['deleted', 'onlyTrashed'])){
            $qb->onlyTrashed();
        }
        if(QMRequest::getParamFromReferrer(['withTrashed'])){
            $qb->withTrashed();
        }
    }
    /**
     * @param $qb
     * @param string $defaultOrderColumn If this appears to be ignored, click reset button because this is kept in localstorage due to "remember" option
     * @param string $defaultDir
     */
    public static function buildDataTableQueryFromRequest($qb, string $defaultOrderColumn, string $defaultDir = 'desc'){
        QueryBuilderHelper::addOrderByFromRequestIfNecessary($qb, $defaultOrderColumn, $defaultDir);
        QueryBuilderHelper::addFilterParamsFromReferrerIfNecessary($qb);
    }
    /**
     * @param \Illuminate\Database\Eloquent\Builder $qb
     */
    public static function addErroredClauses($qb){
        $model = $qb->getModel();
        $fields = $model->getColumns();
        foreach($fields as $field){
            if(stripos($field, 'error') !== false){
                $qb->orWhereNotNull($model->getTable().'.'.$field);
            }
            if($field === UserVariableRelationship::FIELD_STATUS){
                $qb->orWhere($model->getTable().'.'. UserVariableRelationship::FIELD_STATUS, "=", CorrelationStatusProperty::STATUS_ERROR);
            }
        }
        $qb->withTrashed();
    }
    private static function filterAllowedDBQueryParams(array $params = null): array {
        if($params === null && self::isWidgetRequest()){
            throw new \LogicException("Can't get where in widget requests.  Do this in the widget controller");
        }
        if($params === null){$params = $_GET;}
        $blackList = [
	        BaseAccessTokenProperty::URL_PARAM_NAME,
            "sort",
            "limit",
            "offset",
        ];
        $arr = [];
        foreach($params as $key => $value){
            if(in_array($key, $blackList)){continue;}
            $arr[$key] = $value;
        }
        return $arr;
    }
    public static function getHumanizedWhereClause(array $params, string $table = null): string {
        $params = self::filterAllowedDBQueryParams($params);
        if(empty($params)){return "";}
        $arr = [];
        foreach($params as $key => $value){
            $arr[] = self::whereParamsToHumanString($key, $table ?? QMRequest::getTable(), $value);
        }
        $str = implode(" and ", $arr);
        if(strpos($str, "never never")){
            le($str);
        }
        return $str;
    }
	/**
	 * @param string $column
	 * @param string $table
	 * @param $value
	 * @param null $operator
	 * @return string
	 */
	public static function whereParamsToHumanString(string $column, string $table, $value, $operator = null):string{
        $constraint = new Constraint($column, $table, $value, $operator);
        $map = [
            UserVariableRelationship::FIELD_INTERNAL_ERROR_MESSAGE.self::OPERATOR_NOT_NULL => self::HAVE_AN_ERROR,
            UserVariableRelationship::FIELD_ANALYSIS_ENDED_AT.self::OPERATOR_IS_NULL => "that have never been analyzed",
            UserVariableRelationship::FIELD_ANALYSIS_ENDED_AT.self::OPERATOR_NOT_NULL => "that have been analyzed",
        ];
        // Why?  We're filtering by integer id sometimes? if(!is_string($value)){le('!is_string($value)');}
        $lowerKey = QMStr::snakize($column).strtolower($value);
        if(isset($map[$lowerKey])){
            return $map[$lowerKey];
        }
        if($column === "withTrashed"){
            if($value){
                return "including soft-deleted ones";
            }
            return "excluding soft-deleted ones";
        }
        return $constraint->humanize();
    }
    /** @noinspection PhpUnused */
    public static function getHumanizedWhereHtml(array $params = null): string {
		if(self::isWidgetRequest()){le("Can't get where in widget requests.  Do this in the widget controller");}
        $params = self::filterAllowedDBQueryParams($params);
        $list = "";
        foreach($params as $key => $value){
            $list .= "
<li>
    ".self::whereParamsToHumanString($key, QMRequest::getTable(), $value)."
</li>
";
        }
        return "<p>WHERE </p>
            <ul>
            $list
            </ul>";
    }
    /**
     * @param string $table
     * @param $wheres
     * @return string[]
     */
    public static function addWhereClausesStringsFromRequest(string $table, $wheres): array{
        if (is_string($wheres)) {
            $wheres = str_ireplace('where ', '', $wheres);
            $wheres = strtolower($wheres);
            $wheres = explode(' and ', $wheres);
        }
        $columns = BaseModel::getColumnsForTable($table);
        if (!self::isWidgetRequest()) {
            foreach ($columns as $column) {
                $reqParam = QMRequest::getParam($column);
                if ($reqParam !== null) {
                    $reqParam = QMRequest::getParam($column);
                    if (is_numeric($reqParam)) {
                        $wheres[] = "$column = $reqParam";
                    } else {
                        $wheres[] = "$column = '$reqParam'";
                    }
                }
            }
        }
        return $wheres ?? [];
    }
    public static function isWidgetRequest(): bool {
        $path = qm_request()->getRequestUri();
        return $path && stripos($path, "/arrilot/load-widget") !== false;
    }

    /**
     * @param Builder|\Illuminate\Database\Eloquent\Builder $qb
     * @throws AccessTokenExpiredException
     */
    public static function restrictQueryBasedOnPermissions($qb){
        if(!AppMode::isApiRequest()){
            return;
        }
        if($qb instanceof BaseModel){
            $qb = $qb->getQuery();
        }
        if($qb instanceof \Illuminate\Database\Eloquent\Builder){
            $qb = $qb->getQuery();
        }
        $user = QMAuth::getUser();
        if($user && $user->isAdmin()){return;}
        $table = $qb->from;
        $class = BaseModel::getClassByTable($table);
        /** @var BaseModel $model */
        $model = new $class();
        $model->restrictQueryBasedOnPermissions($qb, $user);
    }
    /**
     * @param QMQB|Builder $qb
     * @param array $params
     * @param array|null $aliasToFieldNameMap
     */
    public static function addParams($qb, array $params, array $aliasToFieldNameMap = null): void {
        static::applyFilters($qb, $params);
        static::applyOffsetLimitSort($qb,$params, $aliasToFieldNameMap);
    }
    /**
     * @param \Illuminate\Database\Eloquent\Builder $eqb
     */
    public static function applyTablePrefixedParamsFromRequest(\Illuminate\Database\Eloquent\Builder $eqb): void{
        $dbqb = $eqb->getQuery();
        /** @var BaseModel $m */
        $m = $eqb->getModel();
        $params = $_GET ?? [];
        foreach($params as $name => $value){
            $field = $m->validFilterField($name);
            if($field){
                QueryBuilderHelper::applySingleFilterParam($dbqb, $field, $value);
            }
        }
    }
    /**
     * @return string[]
     */
    public static function getColumnsInRequest(): array {
        return QMRequest::getGetColumnsFromRequest();
    }
    public static function getParamsFromRequest():array {
        return QMRequest::getQueryParams();
    }
    /**
     * @param Builder|\Illuminate\Database\Eloquent\Builder $qb
     * @return string
     */
    public static function dump($qb): string {
        if($qb instanceof \Illuminate\Database\Eloquent\Builder){$qb = $qb->getQuery();}
        $sql = QMQB::addBindingsToSql($qb);
        $formatted = self::formatSQL($sql, false);
        \App\Logging\ConsoleLog::info("================================
$formatted
================================");
        return $sql;
    }
    /**
     * @param Builder|\Illuminate\Database\Eloquent\Builder $qb
     * @return string
     */
    public static function toPreparedSQL($qb): string {
        return self::dump($qb);
    }
    public static function formatSQL(string $sql, $highlight = false): string{
        return SqlFormatter::format($sql, $highlight);
    }
    /**
     * @param string $table
     * @param Builder $qb
     * @return string
     */
    public static function aliasToTable(string $table, Builder $qb): string{
        $original = $table;
        if(Writable::tableExists($table)){return $table;}
        if(stripos($qb->from," as $table") !== false){
            $arr = explode(" ", $qb->from);
            $table = $arr[0];
        } else {
            foreach($qb->joins as $join){
                $joinTable = $join->table;
                if(stripos($joinTable, $table." as ") === 0){
                    $arr = explode(" ", $joinTable);
                    $table = $arr[0];
                }
            }
        }
        // TODO: Handle aliases better if(!Writable::tableExists($table)){le("Table $original not found.  Got $table after parsing alias.");}
        return $table;
    }
}
