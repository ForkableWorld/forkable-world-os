<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Charts\QMHighcharts\Options\BaseLabels;
use App\Charts\QMHighcharts\Options\BaseYAxis;
class YAxis extends BaseYAxis {
	/** @noinspection PhpMissingParentConstructorInspection */
	public function __construct(){
		$this->title = new Title();
		$this->title->enabled = true;
	}
	public function setTitleText(string $title): void{
		$this->title->text = $title;
	}
	public function setColor(string $hex): self{
		// le("Text color should be set in theme!");
		// We can set axis color because we want it to match series when we have multiple series
		$this->getBaseTitle()->setColor($hex);
		$this->getLabels()->setColor($hex);
		//$this->alternateGridColor = $this->lineColor = $this->minorTickColor = $this->tickColor = $hex;
		return $this;
	}
	private function getLabels(): BaseLabels{
		if(!$this->labels){
			$this->labels = new BaseLabels();
		}
		return $this->labels;
	}
	public function getColor(): string{
		return $this->getBaseTitle()->getStyle()->getColor();
	}
}
