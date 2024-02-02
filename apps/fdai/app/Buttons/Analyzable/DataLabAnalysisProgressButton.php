<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Analyzable;
use App\Buttons\DataLabIndex\DataLabSelectQueryButton;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\UI\IonIcon;
class DataLabAnalysisProgressButton extends DataLabSelectQueryButton {
	public $fontAwesome = FontAwesome::CALCULATOR_SOLID;
	public $ionIcon = IonIcon::charts;
	public $image = ImageUrls::BASIC_FLAT_ICONS_CALCULATOR;
	public function __construct($tableOrModel = null){
		parent::__construct($tableOrModel, [
			'sort' => '-analysis_ended_at',
			'view' => 'analysis-progress',
		]);
		$m = $this->getModel();
		$this->setTextAndTitle("Analysis Progress");
		$this->setTooltip("Configurable data tables displaying recent analyses and charts displaying analysis progress over time. ");
	}
}
