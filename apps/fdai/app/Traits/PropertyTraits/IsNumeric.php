<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Exceptions\InvalidAttributeException;
use App\Models\BaseModel;
use App\Storage\DB\QMQB;
use App\Utils\Stats;
use Illuminate\Database\Eloquent\Collection;
use App\Fields\Number;
trait IsNumeric {
	abstract public function getParentModel(): BaseModel;

    /**
     * @throws InvalidAttributeException
     */
    protected function validateMin(): void{
		$valueInCommonUnit = $this->getDBValue();
		$min = $this->getMinimum();
		if($min === null){
			return;
		}
		if($valueInCommonUnit === null){
			return;
		}
		try {
			$less = Stats::lessThan($valueInCommonUnit, $min);
		} catch (\Throwable $e) {
			le("Could not less than " . \App\Logging\QMLog::print_r($valueInCommonUnit, true) . " because " . $e->getMessage());
		}
		if($less){
			$this->throwException("must be at least a minimum of $min but is $valueInCommonUnit");
		}
	}
	public static function whereTooSmall(): ?QMQB{
		$property = new static();
		$minimum = $property->getMinimum();
		if($minimum === null){
			return null;
		}
		$qb = static::whereQMQB("<", $minimum);
		return $qb;
	}
	/**
	 * @throws InvalidAttributeException
	 */
	protected function globalValidation(){
		$this->validateNotNull();
		// Uncomment for debugging infinite loops $this->logInfo("Validating ".(new \ReflectionClass(static::class))->getShortName()."...");
		$this->validateType();
		$this->validateMin();
		$this->validateMax();
	}
	public static function whereTooBig(): ?QMQB{
		$property = new static();
		$max = $property->getMaximum();
		if($max === null){
			return null;
		}
		$qb = static::whereQMQB(">", $max, "too big");
		return $qb;
	}
	/**
	 * @return void
	 * @throws InvalidAttributeException
	 */
	protected function validateMax(): void{
		$max = $this->getMaximum();
		if($max === null){
			return;
		}
		$val = $this->getDBValue();
		if($val === null){
			return;
		}
		if(Stats::greaterThan($val, $max)){
			$this->throwException("must be at most a maximum of $max but is $val");
		}
	}
	/**
	 * @return BaseModel[]|Collection
	 */
	public static function fixTooBig(): array{
		$models = [];
		$qb = static::whereTooBig();
		if(!$qb){
			return [];
		}
		$ids = static::pluckIds($qb);
		foreach($ids as $id){
			$models[] = static::handleTooBig($id);
		}
		return $models;
	}
	/**
	 * @return BaseModel[]|Collection
	 */
	public static function setNullWhereTooBig(): array{
		$qb = static::whereTooBig();
		if(!$qb){
			return [];
		}
		$models = static::setNullWhere($qb, "too big");
		return $models;
	}
	/**
	 * @return BaseModel[]|Collection
	 */
	public static function fixTooSmall(): array{
		$models = [];
		$qb = static::whereTooSmall();
		if(!$qb){
			return [];
		}
		$ids = static::pluckIds($qb);
		foreach($ids as $id){
			$models[] = static::handleTooSmall($id);
		}
		return $models;
	}
	/**
	 * @return BaseModel[]|Collection
	 */
	public static function setNullWhereTooSmall(): array{
		$qb = static::whereTooSmall();
		if(!$qb){
			return [];
		}
		return static::setNullWhere($qb, "too small");
	}
	public static function getIdsWhereTooSmall(): ?\Illuminate\Support\Collection{
		$qb = static::whereTooSmall();
		if(!$qb){
			return null;
		}
		return static::pluckIds($qb);
	}
	/**
	 * @return \Illuminate\Support\Collection
	 */
	public static function getIdsWhereTooBig(): ?\Illuminate\Support\Collection{
		$qb = static::whereTooBig();
		if(!$qb){
			return null;
		}
		return static::pluckIds($qb);
	}
	/**
	 * @param int $id
	 * @noinspection PhpUnusedParameterInspection
	 * @return BaseModel
	 */
	protected static function handleTooBig(int $id): BaseModel{
		le("Please implement " . __METHOD__);
		throw new \LogicException();
	}
	/**
	 * @param int $id
	 * @noinspection PhpUnusedParameterInspection
	 * @return BaseModel
	 */
	protected static function handleTooSmall(int $id): BaseModel{
		le("Please implement " . __METHOD__);
		throw new \LogicException();
	}
	protected function getNumericStepSize(): ?float{
		return null;
	}
	protected function getSigFigs(): int{
		return 2;
		//return QMCorrelation::SIG_FIGS;
	}
	/**
	 * @param Number $f
	 */
	protected function addMinMaxStepToField(Number $f): void{
		$model = $this->getParentModel();
		if(!$model->attributesToArray()){
			return;
		}
		$min = $this->getMinimum();
		if($min !== null){
			$f->min($min);
		}
		$max = $this->getMaximum();
		if($max !== null){
			$f->max($max);
		}
		$step = $this->getNumericStepSize();
		if($step !== null){
			$f->step($step);
		}
	}
}
