<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\GlobalVariableRelationshipCharts;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\ScatterHighchartConfig;
use App\Models\GlobalVariableRelationship;
use App\Types\QMStr;
class PopulationTraitCorrelationScatterPlot extends GlobalVariableRelationshipChart {
	/**
	 * AverageValueScatterPlot constructor.
	 * @param GlobalVariableRelationship|null $c
	 */
	public function __construct($c = null){
		if(!$c){
			return;
		}
		$this->sourceObject = $c;
		parent::__construct($c,
			"Trait User Variable Relationship Between " . $this->getCauseName() . " and " . $this->getEffectName());
		$this->setExplanation("People with higher " . $this->getCauseName() . " usually have " .
			$this->getHigherLower() . " " . $this->getEffectName());
	}
	/**
	 * @return GlobalVariableRelationship
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function getCorrelation(){
		return $this->getSourceObject();
	}
	/**
	 * @return ScatterHighchartConfig
	 */
	public function generateHighchartConfig(): HighchartConfig{
		$c = $this->getCorrelation();
		$pairsOfAveragesForAllUsers = $c->getPairsOfAveragesForAllUsers();
		$arr = $this->setXyVariableValues($pairsOfAveragesForAllUsers);
		$causeUnit = $c->getCauseVariableCommonUnit()->abbreviatedName;
		$effectUnit = $c->getEffectVariableCommonUnit()->abbreviatedName;
		$causeName = $c->getCauseVariableName();
		$effectName = $c->getEffectVariableName();
		$config = new ScatterHighchartConfig($c, $this);
		$config->setTitle($this->getTitleAttribute());
		$config->setSubtitle($this->getSubtitleAttribute());
		$config->addSeriesArray($c->getEffectVariableName() . ' by ' . $c->getCauseVariableName(), $arr);
		$config->setXAxisTitleText("Average " . $this->getCauseName() . ' (' . $this->getCauseUnitAbbreviatedName() .
			') for Participant');
		$config->setYAxisTitle("Average " . $this->getEffectName() . ' (' . $this->getEffectUnitAbbreviatedName() .
			') for Participant');
		$effectName = QMStr::escapeSingleQuotes($effectName);
		$causeName = QMStr::escapeSingleQuotes($causeName);
		$config->setTooltipFormatter("
            return 'People with an average of ' + this.x +
                '$causeUnit $causeName<br/> typically exhibit an average of <br/>' +
                this.y + '$effectUnit $effectName';
        ");
		return $this->setHighchartConfig($config);
	}
	/**
	 * @return string
	 */
	private function getHigherLower(): string{
		$higherLower = ($this->getSourceObject()->getPopulationTraitCorrelationPearsonCorrelationCoefficient() >
			0) ? "higher" : "lower";
		return $higherLower;
	}
	/**
	 * @param $pairsOfAveragesForAllUsers
	 * @return array
	 */
	public function setXyVariableValues(array $pairsOfAveragesForAllUsers): array{
		foreach($pairsOfAveragesForAllUsers as $pair){
			$this->xyVariableValues[] = [
				$pair->causeVariableAverageValue,
				$pair->effectVariableAverageValue,
			];
			$this->dataX[] = $pair->causeVariableAverageValue;
			$this->dataY[] = $pair->effectVariableAverageValue;
		}
		return $this->xyVariableValues;
	}
}
