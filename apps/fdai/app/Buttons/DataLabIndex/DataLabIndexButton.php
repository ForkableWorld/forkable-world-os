<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLabIndex;
use App\Buttons\QMButton;
use App\Models\BaseModel;
class DataLabIndexButton extends QMButton {
	/**
	 * @param BaseModel|string $class
	 * @param array $params
	 */
	public function __construct($class, array $params = []){
		$title = $class::getClassNameTitlePlural();
		$this->setImage($class::DEFAULT_IMAGE);
		$this->setBackgroundColor($class::COLOR);
		$this->setFontAwesome($class::FONT_AWESOME);
		$this->setTooltip($class::CLASS_DESCRIPTION ?? "See $title");
		$this->setUrl($class::generateDataLabIndexUrl($params));
		parent::__construct($title);
	}
}
