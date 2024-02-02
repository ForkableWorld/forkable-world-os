<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons;
use App\Models\BaseModel;
use DigitalCreative\CollapsibleResourceManager\Resources\AbstractResource;
class AstralIndexButton extends AstralButton {
	/**
	 * AstralButton constructor.
	 * @param BaseModel|string $class
	 * @param string|null $label
	 */
	public function __construct(string $class, string $label = null, $badgeText = null, string $fontAwesome = null,
		string $tooltip = null, string $color = null, string $url = null, array $params = []){
		$this->class = $class;
		if($params){
			$this->parameters = array_merge($this->parameters, $params);
		}
		if($badgeText){
			$this->setBadgeText($badgeText);
		}
		parent::__construct($label ?? $class::getClassNameTitlePlural());
		$this->setTooltip($tooltip ?? $class::getClassDescription());
		$this->setFontAwesome($fontAwesome ?? $class::getClassFontAwesome());
		if($color){
			$this->setBackgroundColor($color);
		}
		if(!$url){
			$url = $class::getDataLabIndexUrl($params);
		}
		$this->setUrl($url, $params);
	}
	public function getAstralMenuItem(): AbstractResource{
		return parent::getAstralMenuItem()->index();
	}
}
