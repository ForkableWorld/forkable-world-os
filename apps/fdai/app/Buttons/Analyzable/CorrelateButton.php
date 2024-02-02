<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Analyzable;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\UserVariable;
use App\Models\Variable;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class CorrelateButton extends QMButton {
	public $fontAwesome = FontAwesome::CALCULATOR_SOLID;
	public $image = ImageUrls::CALCULATOR;
	/**
	 * DeleteButton constructor.
	 * @param BaseModel|UserVariable|Variable $model
	 */
	public function __construct(BaseModel $model){
		parent::__construct("Correlate");
		$this->setTooltip("Correlate " . $model->getTitleAttribute());
		$this->setUrl($model->getCorrelateUrl());
	}
}
