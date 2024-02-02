<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Analyzable;
use App\Buttons\QMButton;
use App\Traits\HasCharts;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class ChartsButton extends QMButton {
	/**
	 * ChartsButton constructor.
	 * @param HasCharts|\App\Models\Variable $variable
	 */
	public function __construct($variable){
		$outcomesOrPredictors = $variable->isOutcome() ? 'Predictors' : 'Outcomes';
		parent::__construct("See $outcomesOrPredictors of ".$variable->getTitleAttribute() . " & Charts");
		$this->setFontAwesome(FontAwesome::CHART_LINE_SOLID);
		$this->setImage(ImageUrls::BASIC_FLAT_ICONS_BAR_CHART);
		$this->setUrl($variable->getChartsUrl());
		$this->setTooltip("See {$variable->getTitleAttribute()} data visualizations");
	}
}
