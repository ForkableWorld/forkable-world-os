<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Model;
use App\Models\BaseModel;
use App\Traits\DataLabTrait;
use App\UI\FontAwesome;
use App\UI\HtmlHelper;
use App\UI\ImageUrls;
class DataLabDeleteButton extends ModelButton {
	public $fontAwesome = FontAwesome::TRASH_SOLID;
	public $image = ImageUrls::BASIC_FLAT_ICONS_TRASH;
	/**
	 * DeleteButton constructor.
	 * @param BaseModel|DataLabTrait $model
	 */
	public function __construct($model){
		parent::__construct($model);
		$this->setTextAndTitle("Delete");
		$this->setTooltip("Delete " . $model->getTitleAttribute());
		$this->setUrl($model->getDataLabDeleteUrl());
	}
	/**
	 * @param string $class
	 * @param string $style
	 * @return string|null
	 */
	public function getLink(string $class = "", string $style = ""): string{
		$form = HtmlHelper::generateDeleteForm($this->getUrl());
		// javascript:void() Prevents jumping to the top of page
		return "
            <a href='javascript:void(0)' title='$this->tooltip' class='$class' style='$style'>
                $form
            </a>
        ";
	}
}
