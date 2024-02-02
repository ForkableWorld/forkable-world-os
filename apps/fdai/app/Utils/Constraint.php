<?php namespace App\Utils;
use App\Models\BaseModel;
use App\Storage\DB\Writable;
use App\Storage\QueryBuilderHelper;
use App\Types\QMStr;
use App\Types\TimeHelper;
use Carbon\Carbon;
use Doctrine\DBAL\Schema\Table;
use Illuminate\Database\Query\Builder;
class Constraint {
	const OPERATOR_EQUAL = '=';
	const OPERATOR_NOT_EQUAL = '<>';
	const OPERATOR_GREATER_EQUAL = '>=';
	const OPERATOR_GREATER = '>';
	const OPERATOR_LESS_EQUAL = '<=';
	const OPERATOR_LESS = '<';
	const OPERATOR_LIKE = 'like';
	const OPERATOR_NOT_LIKE = 'not like';
	const OPERATOR_NOT_NULL = 'not null';
	const OPERATOR_IN = 'in';
	const OPERATOR_NOT_IN = 'not in';
	const MODE_AND = 'and';
	const MODE_OR = 'or';
	const GT = 'gt';
	const GE = 'ge';
	const GTE = 'gte';
	const LT = 'lt';
	const LE = 'le';
	const LTE = 'lte';
	public $operator;
	public $value;
	public $is_negation;
	public $column;
	/**
	 * @var Builder
	 */
	protected $qb;
	public $table;
	/**
	 * @param string $column
	 * @param string $table
	 * @param null $value value
	 * @param null $operator operator
	 */
	public function __construct(string $column, string $table, $value = null, $operator = null){
		$this->table = $table;
		if(!is_array($value)){
			$value = trim($value, ", \t\n\r\0\x0B");
		}
		$this->value = $value;
		$arr = explode('.', $column);
		if(isset($arr[1])){
			$this->column = $arr[1];
			$this->table = $arr[0];
		} else{
			$this->column = $arr[0];
		}
		$this->operator = $operator;
		$this->parseIsNegation();
		$this->getOperatorFromValueIfNecessary();
	}
	/**
	 * @return string
	 */
	public function getOperator(){
		return $this->operator;
	}
	/**
	 * @return string
	 */
	public function getValue(){
		return $this->value;
	}
	public function getMethod($mode = Constraint::MODE_AND){
		if($this->operator == Constraint::OPERATOR_IN){
			return $mode != static::MODE_OR ? 'whereIn' : 'orWhereIn';
		} elseif($this->operator == Constraint::OPERATOR_NOT_IN){
			return $mode != static::MODE_OR ? 'whereNotIn' : 'orWhereNotIn';
		} else{
			return $mode != static::MODE_OR ? 'where' : 'orWhere';
		}
	}
	/**
	 * Applies constraint to query.
	 * @param Builder|\Illuminate\Database\Eloquent\Builder $qb query builder
	 */
	public function apply($qb){
		if($qb instanceof \Illuminate\Database\Eloquent\Builder){
			$qb = $qb->getQuery();
		}
		$this->qb = $qb;
		$field = $this->column;
		if($t = $this->table){
			$field = $t . '.' . $field;
		}
		$value = $this->value;
		if($value === 'Anything'){
			return;
		}
		$isEmptyNameFilter = str_contains(strtolower($field), 'name') && empty($value);
		if($isEmptyNameFilter){
			return;
		}
		if($this->isNotNullFilter()){
			$qb->whereNotNull($field);
			return;
		}
		if($this->isNullFilter()){
			$qb->whereNull($field);
			return;
		}
		if($this->isNameSearch()){
			$value = $this->getSearchableValue();
			$qb->whereRaw($field . " " . \App\Storage\DB\ReadonlyDB::like() . " '" . $value . "'");
			return;
		}
		if($this->isWhereNameFilter()){
			$qb->where($field, $value);
			return;
		}
		if($this->operator == Constraint::OPERATOR_IN){
			$qb->whereIn($field, $value);
		} elseif($this->operator == Constraint::OPERATOR_NOT_IN){
			$qb->whereNotIn($field, $value);
		} else{
			$model = $this->getModel();
			$prop = $model->getPropertyModel($this->column);
			if($prop && $prop->isDateTime()){
				$unixtime = time_or_null($value);
				if($unixtime < TimeHelper::YEAR_2000_UNIXTIME ||
				   !TimeHelper::timestampIsReasonable($unixtime)){
					$unixtime = null;
				}
				if($unixtime){
					$qb->where($field, $this->operator, 
					               Carbon::createFromTimestampUTC($unixtime));
					return;
				}
			}
			$qb->where($field, $this->operator, $value);
		}
	}
	/**
	 * Check if query constraint is negated.
	 */
	protected function parseIsNegation(): void{
		if(is_array($this->value)) return;
		if(preg_match('/^!/', $this->value)){
			$this->value = preg_replace('/^!/', '', $this->value);
			$this->is_negation = true;
		}
		if(strpos($this->value, '(ne)') === 0){
			$this->value = str_replace('(ne)', '', $this->value);
			$this->is_negation = true;
		}
	}
	protected function parseComparisonOperator(): void{
		if(preg_match('/^\((gt|gte|ge|lt|le|lte)\)(.+)$/', $this->value, $match)){
			$this->operator = null;
			switch($match[1]) {
				case self::GT:
					$this->operator = $this->is_negation ? static::OPERATOR_LESS_EQUAL : static::OPERATOR_GREATER;
					break;
				case self::GTE:
				case self::GE:
					$this->operator = $this->is_negation ? static::OPERATOR_LESS : static::OPERATOR_GREATER_EQUAL;
					break;
				case self::LT:
					$this->operator = $this->is_negation ? static::OPERATOR_GREATER_EQUAL : static::OPERATOR_LESS;
					break;
				case self::LTE:
				case self::LE:
					$this->operator = $this->is_negation ? static::OPERATOR_GREATER : static::OPERATOR_LESS_EQUAL;
					break;
			}
			$this->value = $match[2];
		}
	}
	protected function parseLikeOperator(){
		if(preg_match('/(^%.+)|(.+%$)/', $this->value)){
			$this->operator = $this->is_negation ? static::OPERATOR_NOT_LIKE : static::OPERATOR_LIKE;
		}
	}
	/**
	 * @return void
	 */
	protected function parseEqualsInOperator(){
		$val =
			$this->value;  // This prevents us from searching for strings with commas and I don't think we need whereIn anyway
		if(!is_array($val) && strpos($val, ',') !== false){
			$this->value = preg_split('/,/', $val);
		}
		if($this->is_negation){
			$this->operator = is_array($this->value) ? static::OPERATOR_NOT_IN : static::OPERATOR_NOT_EQUAL;
		} else{
			$this->operator = is_array($this->value) ? static::OPERATOR_IN : static::OPERATOR_EQUAL;
		}
	}
	/**
	 * @return bool
	 */
	protected function isNullFilter(): bool{
		$value = $this->value;
		$isNull = QMStr::isNullString($value);
		if($value === null){
			$isNull = true;
		}
		return $isNull;
	}
	/**
	 * @return bool
	 */
	protected function isNameSearch(): bool{
		$field = $this->column;
		$value = $this->getSearchableValue();
		$isLikeName = is_string($value) && strpos(strtolower($field), 'name') !== false && substr($value, 0, 1) === '%';
		return $isLikeName;
	}
	/**
	 * @return bool
	 */
	protected function isWhereNameFilter(): bool{
		return is_string($this->value) && (strpos($this->column, 'name') !== false);
	}
	/**
	 * @return string
	 */
	protected function getSearchableValue(){
		$value = $this->value;
		if($value !== null && is_string($value)){
			$value = str_replace('*', '%', $value);
		}
		return $value;
	}
	/**
	 * @return bool
	 */
	protected function isNotNullFilter(): bool{
		$value = $this->value;
		if(!is_string($value)){
			return false;
		}
		$value = strtolower(str_replace("+", " ", $value));
		$isNotNull = $value === QueryBuilderHelper::OPERATOR_NOT_NULL;
		return $isNotNull;
	}
	protected function getOperatorFromValueIfNecessary(): void{
		if(!$this->operator){
			$this->parseLikeOperator();
			if(!$this->operator){
				$this->parseComparisonOperator();
			}
			if(!$this->operator){
				if($this->is_negation){
					$this->operator = self::OPERATOR_NOT_EQUAL;
				} else{
					$this->operator = self::OPERATOR_EQUAL;
				}
			}
		}
	}
	public function humanize(): string{
		$humanColumn = $this->getHumanizedColumn();
		if($this->isNotNullFilter()){
			return "that have a $humanColumn";
		}
		$humanOperator = $this->getHumanizedOperator();
		$humanValue = $this->getHumanizedValue();
		if($this->columnIsDateOrTime()){
			$title = "where the $humanColumn $humanOperator $humanValue";
		} else{
			$title = "where the $humanColumn $humanOperator $humanValue";
		}
		if(strpos($title, " At were never never") !== false){
			$title = str_replace(" At were never never", "", $title);
			$title .= " value is not set";
		}
		return $title;
	}
	public function getTable(): Table{
		$db = Writable::db();
		return $db->listTableDetails($this->table);
	}
	public function columnExists(): bool{
		$table = $this->table;
		if(!$table){
			return false;
		}
		return Writable::columnExists($table, $this->column);
	}
	/**
	 * @return string
	 */
	protected function getHumanizedColumn(): string{
		$col = $this->column;
		$humanColumn = QMStr::titleCaseSlow(str_replace('_', ' ', $col));
		$humanColumn = str_replace(" Id", " ID", $humanColumn);
		return $humanColumn;
	}
	/**
	 * @return string
	 */
	protected function getHumanizedOperator(): string{
		$operator = $this->getOperator();
		if($this->isNullFilter()){
			if($this->columnIsDateOrTime()){
				return "were never";
			} else{
				return "do NOT have a";
			}
		}
		if($this->isNotNullFilter()){
			if($this->columnIsDateOrTime()){
				return "were";
			} else{
				return "have a";
			}
		}
		$map = [
			self::OPERATOR_EQUAL => "is",
			self::OPERATOR_NOT_EQUAL => "is not",
			self::OPERATOR_GREATER => "is greater than",
			self::OPERATOR_LESS => "is less than",
			self::OPERATOR_GREATER_EQUAL => "is greater than or equal to",
			self::OPERATOR_LESS_EQUAL => "is less than or equal to",
			self::OPERATOR_LIKE => "contains",
			self::OPERATOR_NOT_LIKE => "does NOT contain",
		];
		$human = $map[$operator];
		if(!$human){
			le("Please map operator $operator");
		}
		return $human;
	}
	public function columnIsDateOrTime(): bool{
		$col = $this->column;
		return strpos($col, '_at') !== false;
	}
	/**
	 * @return string
	 */
	protected function getHumanizedValue(): string{
		$value = $this->getValue();
		if($this->isNullFilter()){
			if($this->columnIsDateOrTime()){
				return "never";
			} else{
				return "is null";
			}
		}
		if($this->isNotNullFilter()){
			if($this->columnIsDateOrTime()){
				return "never";
			} else{
				return "is NOT null";
			}
		}
		return $value;
	}
	/**
	 * @return bool
	 */
	public function isUserId(): bool{
		return stripos($this->column, "user_id") !== false;
	}
	public static function getOperators(): array{
		return [
			self::OPERATOR_EQUAL,
			self::OPERATOR_NOT_EQUAL,
			self::OPERATOR_GREATER_EQUAL,
			self::OPERATOR_GREATER,
			self::OPERATOR_LESS_EQUAL,
			self::OPERATOR_LESS,
			self::OPERATOR_LIKE,
			self::OPERATOR_NOT_LIKE,
			self::OPERATOR_IN,
			self::OPERATOR_NOT_IN,
		];
	}
	public static function fromWhere(array $where, string $table): ?self{
		$col = $where["column"] ?? null;
		$val = $where["value"] ?? null;
		$operator = $where["operator"] ?? null;
		$type = $where["type"] ?? null;
		if($col){
			if($type){
				if(strtolower(str_replace(" ", "", $type)) === "notnull"){
					$val = self::OPERATOR_NOT_NULL;
				}
			}
			if($val === null){
				le($where);
			}
			$const = new Constraint($col, $table, $val, $operator);
			//if(!$const->columnExists()){return null;}
			return $const;
		}
		$operators = self::getOperators();
		$sql = $where["sql"];
		$sql = strtolower($sql);
		foreach($operators as $operator){
			$arr = explode($operator, $sql);
			if(count($arr) > 1){
				$const = new Constraint($arr[0], $table, $arr[1], $operator);
				//if(!$const->columnExists()){return null;}
				return $const;
			}
		}
		throw new \LogicException("please implement parser for " . \App\Logging\QMLog::print_r($where, true));
	}
	/**
	 * @param array $array
	 * @return static
	 */
	public static function __set_state(array $array): self{
		$object = new static;
		foreach($array as $key => $value){
			$object->{$key} = $value;
		}
		return $object;
	}
	private function getModel(): ?BaseModel {
		$baseModel = BaseModel::findModelByTable($this->table);
		return $baseModel;
	}
}/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */
