<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Links;
use App\Buttons\QMButton;
use App\Http\Controllers\BaseDataLabController;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class DataLabButton extends QMButton {
	public $fontAwesome = FontAwesome::CALCULATOR_SOLID;
	public $image = ImageUrls::CALCULATOR;
	public $title = 'DataLab';
	public $tooltip = "The DataLab administrative backend provides a powerful tool to create reports, drill down, clean data, monitor progress through the analytical pipeline, and identify issues.  The DataLab also provides easy navigation between dozens of relationships between the various model class via a data graph. ";
	public function __construct(string $text = null, string $ionIcon = null, string $backgroundColor = null,
		string $url = null, $additionalInformation = null){
		parent::__construct($text, $url, $backgroundColor, $ionIcon, $additionalInformation);
		$this->setUrl(BaseDataLabController::getUrl());
	}
}
