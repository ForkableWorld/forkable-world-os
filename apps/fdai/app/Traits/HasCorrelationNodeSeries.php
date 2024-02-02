<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Charts\QMHighcharts\CorrelationNodeSeries;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseTooltip;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Models\GlobalVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Variables\QMCommonVariable;
use Google\Collection;
trait HasCorrelationNodeSeries {
	protected $correlations;
	protected $variableCategoryName;
	protected $limit;
	abstract public function getTitleAttribute(): string;
	abstract public function getSubtitleAttribute(): string;
	abstract protected function getTooltip(): BaseTooltip;
	/**
	 * @return GlobalVariableRelationship[]|UserVariableRelationship[]|Collection
	 */
	public function getOutcomesOrPredictors(){
		if($c = $this->correlations){
			return $c;
		}
		$v = $this->getQMVariable();
		if($v instanceof QMCommonVariable){
			$correlations = $v->getPublicOutcomesOrPredictors()->take($this->limit);
		} else{
			$correlations = $v->getOutcomesOrPredictors($this->limit, $this->variableCategoryName);
		}
		return $this->correlations = $correlations;
	}
	/**
	 * @param GlobalVariableRelationship[]|UserVariableRelationship[]|Collection $correlations
	 */
	public function setCorrelations($correlations): void{
		$this->correlations = $correlations;
	}
	/**
	 * @return string
	 */
	public function getVariableCategoryName(): ?string{
		return $this->variableCategoryName;
	}
	/**
	 * @param string $variableCategoryName
	 */
	public function setVariableCategoryName(string $variableCategoryName): void{
		$this->variableCategoryName = $variableCategoryName;
	}
	/**
	 * @return HighchartConfig
	 * @throws NotEnoughDataException
	 * @throws TooSlowToAnalyzeException
	 */
	public function generateHighchartConfig(): HighchartConfig{
		$variable = $this->getQMVariable();
		$config = parent::generateHighchartConfig();
		$correlations = $this->getOutcomesOrPredictors();
		$s = new CorrelationNodeSeries($this->getTitleAttribute(), $variable, $correlations);
		$this->addSeries($s);
		$this->addUrlClickEvent();
		$this->setTooltip($this->getTooltip());
		$config->deletePlotOptions();
		return $config;
	}
}
