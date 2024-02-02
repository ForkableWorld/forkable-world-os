<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\CorrelationCharts;
use App\Charts\QMChart;
use App\Slim\Model\Measurement\Pair;
use App\Traits\HasCorrelationCoefficient;
use App\Variables\QMUserVariable;
abstract class CorrelationChart extends QMChart {
	protected $pairs;
	protected $dataX = [];
	protected $dataY = [];
	protected $xyVariableValues = [];
	/**
	 * PairsOverTimeLineChart constructor.
	 * @param HasCorrelationCoefficient $c
	 * @param string $title
	 */
	public function __construct($c, string $title){
		parent::__construct(null, $c, $title);
	}
	/**
	 * @return HasCorrelationCoefficient
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function getCorrelation(){
		return $this->getSourceObject();
	}
	/**
	 * @return string
	 */
	protected function getCauseName(): string{
		return $this->getCauseVariableDisplayName();
	}
	/**
	 * @return string
	 */
	protected function getEffectUnitAbbreviatedName(): string{
		$name = $this->getSourceObject()->getEffectVariableCommonUnitAbbreviatedName();
		if($name === "yes/no"){
			return "count";
		}
		return $name;
	}
	/**
	 * @return string
	 */
	protected function getCauseUnitAbbreviatedName(): string{
		if(!$this->sourceObject){
			le("Please set correlationObject");
		}
		$name = $this->getSourceObject()->getCauseVariableCommonUnitAbbreviatedName();
		if($name === "yes/no"){
			return "count";
		}
		return $name;
	}
	/**
	 * @return string
	 */
	protected function getEffectName(): string{
		return $this->getEffectVariableDisplayName();
		//return $this->getHasCorrelationCoefficient()->getEffectVariableName();
	}
	/**
	 * @return Pair[]
	 */
	public function getPairs(): array{
		return $this->getCorrelation()->getPairs();
	}
	/**
	 * @return string
	 */
	protected function getCauseVariableDisplayName(): string{
		$s = $this->getSourceObject();
		return $s->causeNameWithSuffix();
	}
	/**
	 * @return string
	 */
	protected function getEffectVariableDisplayName(): string{
		return $this->getSourceObject()->effectNameWithSuffix();
	}
	/**
	 * @return QMUserVariable
	 */
	protected function getCauseQMUserVariable(): QMUserVariable{
		return $this->getSourceObject()->getOrSetCauseQMVariable();
	}
	/**
	 * @return QMUserVariable
	 */
	protected function getEffectQMUserVariable(): QMUserVariable{
		return $this->getSourceObject()->getEffectQMVariable();
	}
}
