<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Measurement;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidVariableValueException;
use App\Slim\Model\QMUnit;
use App\Slim\View\Request\Pair\GetPairRequest;
use App\Traits\HasCauseAndEffect;
use App\Traits\HasModel\HasUserCauseAndEffect;
use App\Utils\Stats;
use App\Variables\QMUserVariable;
use Illuminate\Support\Arr;
/** Cause and effect pairs for the scatter plot and correlation analysis.
 * @package App\Slim\Model
 */
class Pair {
	use HasUserCauseAndEffect, HasCauseAndEffect;
	private $causeUserVariable;
	private $causeValueInUserUnit;
	private $causeVariableId;
	private $effectUserVariable;
	private $effectValueInUserUnit;
	private $effectVariableId;
	/**
	 * @var \App\Slim\View\Request\Pair\GetPairRequest
	 */
	private GetPairRequest $pairRequest;
	private $userId;
	public $causeMeasurementValue;
	public $causeUnitAbbreviatedName;
	public $effectMeasurementValue;
	public $effectUnitAbbreviatedName;
	public $startAt;
	public $timestamp;
	/**
	 * Create effect and cause measurements pair.
	 * @param int $timestamp
	 * @param float $causeValue
	 * @param float $effectValue
	 * @param \App\Slim\View\Request\Pair\GetPairRequest $r
	 */
	public function __construct(int $timestamp, float $causeValue, float $effectValue, GetPairRequest $r){
		$this->timestamp = $timestamp;
		$this->startAt = date('Y-m-d H:i:s', $timestamp);
		$this->causeMeasurementValue = $causeValue;
		$this->effectMeasurementValue = $effectValue;
		$this->pairRequest = $r;
		$this->causeUnitAbbreviatedName = $r->getCauseUnitAbbreviatedName();
		$this->effectUnitAbbreviatedName = $r->getEffectUnitAbbreviatedName();
		$this->setCauseUserVariable($uv = $r->getCauseQMUserVariable());
		$this->setEffectUserVariable($r->getEffectQMUserVariable());
		$this->setUserId($uv->getUserId());
	}
	public static function getAverageCauseValue(array $pairs): float{
		$causeBaselineValues = Arr::pluck($pairs, 'causeMeasurementValue');
		$value = Stats::average($causeBaselineValues, 3);
		return $value;
	}
	/**
	 * @return float
	 */
	public function getCauseValueInUserUnit(): float{
		if($this->causeValueInUserUnit !== null){
			return $this->causeValueInUserUnit;
		}
		$causeValue = $this->causeMeasurementValue;
		if(!$this->userId){
			return $causeValue;
		}
		$userUnit = $this->getCauseUserUnit();
		$unitName = $this->causeUnitAbbreviatedName;
		$cause = $this->getCauseQMUserVariable();
		try {
			return $this->causeValueInUserUnit = QMUnit::convertValue($causeValue, $unitName, $userUnit, $cause, 86400);
		} catch (IncompatibleUnitException $e) {
			/** @var \LogicException $e */
			throw $e;
		} catch (InvalidVariableValueException $e) {
			/** @var \LogicException $e */
			throw $e;
		}
	}
	/**
	 * @return float
	 */
	public function getEffectValueInUserUnit(): float{
		if($this->effectValueInUserUnit !== null){
			return $this->effectValueInUserUnit;
		}
		$effectValue = $this->effectMeasurementValue;
		if(!$this->userId){
			return $effectValue;
		}
		$userUnit = $this->getEffectUserUnit();
		$unitName = $this->effectUnitAbbreviatedName;
		$effect = $this->getEffectQMUserVariable();
		try {
			return $this->effectValueInUserUnit =
				QMUnit::convertValue($effectValue, $unitName, $userUnit, $effect, 86400);
		} catch (IncompatibleUnitException $e) {
			/** @var \LogicException $e */
			throw $e;
		} catch (InvalidVariableValueException $e) {
			/** @var \LogicException $e */
			throw $e;
		}
	}
	/**
	 * @return QMUserVariable
	 */
	public function getCauseQMUserVariable(): QMUserVariable{
		return $this->pairRequest->getCauseQMUserVariable();
	}
	/**
	 * @return QMUserVariable
	 */
	public function getEffectQMUserVariable(): QMUserVariable{
		return $this->pairRequest->getEffectQMUserVariable();
	}
	/**
	 * @return mixed
	 */
	public function getUserId(): ?int{
		return $this->userId;
	}
	/**
	 * @param mixed $userId
	 */
	public function setUserId(int $userId){
		$this->userId = $userId;
	}
	/**
	 * @return int
	 */
	public function getCauseVariableId(): int{
		return $this->causeVariableId;
	}
	/**
	 * @param int $causeVariableId
	 */
	public function setCauseVariableId(int $causeVariableId){
		$this->causeVariableId = $causeVariableId;
	}
	/**
	 * @return int
	 */
	public function getEffectVariableId(): int{
		return $this->effectVariableId;
	}
	/**
	 * @param int $effectVariableId
	 */
	public function setEffectVariableId(int $effectVariableId){
		$this->effectVariableId = $effectVariableId;
	}
	/**
	 * @param QMUserVariable $uv
	 */
	public function setCauseUserVariable(QMUserVariable $uv){
		$this->causeUserVariable = $uv;
		$this->userId = $uv->userId;
		$this->causeVariableId = $uv->variableId;
	}
	/**
	 * @param QMUserVariable $uv
	 */
	public function setEffectUserVariable(QMUserVariable $uv){
		$this->effectUserVariable = $uv;
		$this->userId = $uv->userId;
		$this->effectVariableId = $uv->variableId;
	}
	public function getCauseUserUnit(): QMUnit{
		return $this->getCauseQMUserVariable()->getUserUnit();
	}
	public function getEffectUserUnit(): QMUnit{
		return $this->getEffectQMUserVariable()->getUserUnit();
	}
	public function getCauseUnit(): QMUnit{
		return QMUnit::find($this->causeUnitAbbreviatedName);
	}
	public function getEffectUnit(): QMUnit{
		return QMUnit::find($this->effectUnitAbbreviatedName);
	}
	/**
	 * @return float
	 */
	public function getEffectValue(): float{
		return $this->effectMeasurementValue;
	}
	/**
	 * @return float
	 */
	public function getCauseValue(): float{
		return $this->causeMeasurementValue;
	}
	public function getCauseUserVariableId(): int{
		return $this->getCauseQMUserVariable()->getUserVariableId();
	}
	public function getEffectUserVariableId(): int{
		return $this->getEffectQMUserVariable()->getUserVariableId();
	}
	public function getCauseVariableCategoryId(): int{
		return $this->getCauseVariable()->getVariableCategoryId();
	}
	public function getEffectVariableCategoryId(): int{
		return $this->getEffectVariable()->getVariableCategoryId();
	}
	public function getId(){
		// TODO: Implement getId() method.
	}
}
