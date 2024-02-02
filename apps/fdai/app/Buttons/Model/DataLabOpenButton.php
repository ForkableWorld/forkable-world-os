<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Model;
use App\Models\BaseModel;
use App\Traits\DataLabTrait;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class DataLabOpenButton extends ModelButton {
	public $fontAwesome = FontAwesome::EYE;
	public $image = ImageUrls::BASIC_FLAT_ICONS_EYE;
	/**
	 * DeleteButton constructor.
	 * @param BaseModel|DataLabTrait $model
	 * @param array $params
	 */
	public function __construct($model, array $params = []){
		parent::__construct($model);
		$this->setTextAndTitle("Open");
		$this->setImage($model->getImage());
		$this->setBackgroundColor($model->getColor());
		$this->setTooltip("View " . $model->getTitleAttribute());
		$this->setUrl($model->getDataLabShowUrl($params));
	}
}
