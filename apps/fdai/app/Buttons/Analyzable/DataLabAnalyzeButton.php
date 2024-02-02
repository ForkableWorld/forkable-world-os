<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Analyzable;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Traits\DataLabTrait;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class DataLabAnalyzeButton extends QMButton {
	public $fontAwesome = FontAwesome::CALCULATOR_SOLID;
	public $image = ImageUrls::CALCULATOR;
	/**
	 * DeleteButton constructor.
	 * @param BaseModel|DataLabTrait $model
	 */
	public function __construct($model){
		parent::__construct("Analyze");
		$this->setTooltip("Analyze " . $model->getTitleAttribute());
		$this->setUrl($model->getDataLabAnalyzeUrl());
	}
}
