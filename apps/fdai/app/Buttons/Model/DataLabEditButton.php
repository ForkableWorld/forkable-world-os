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
class DataLabEditButton extends ModelButton {
	public $fontAwesome = FontAwesome::EDIT;
	public $image = ImageUrls::ESSENTIAL_COLLECTION_EDIT;
	/**
	 * DeleteButton constructor.
	 * @param BaseModel|DataLabTrait $model
	 * @param array $params
	 */
	public function __construct($model, array $params = []){
		parent::__construct($model);
		$this->setTextAndTitle("Edit");
		$this->setTooltip("Edit " . $model->getTitleAttribute());
		$this->setUrl($model->getDataLabEditUrl($params));
	}
}
