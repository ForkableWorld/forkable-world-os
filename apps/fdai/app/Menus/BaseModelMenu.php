<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Menus;
use App\Models\BaseModel;
use App\Slim\Model\DBModel;
use App\Utils\QMRoute;
abstract class BaseModelMenu extends QMMenu {
	public $title = ""; // Leave empty so we don't take up so much room
	/**
	 * @var BaseModel
	 */
	protected $model;
	/**
	 * @param null $tableOrModel
	 */
	/**
	 * @param null $tableOrModel
	 */
	public function __construct($tableOrModel = null){
		if($tableOrModel instanceof BaseModel){
			$this->model = $tableOrModel;
		} elseif($tableOrModel instanceof DBModel){
			$this->model = $tableOrModel->l();
		} elseif($tableOrModel){
			$this->model = BaseModel::getInstanceByTable($tableOrModel);
		} else{
			$this->model = QMRoute::getCurrent()->getModel();
		}
		$class = $this->getClass();
		if(!$this->fontAwesome){
			$this->fontAwesome = $class::FONT_AWESOME;
		}
		if(!$this->image){
			$this->image = $class::DEFAULT_IMAGE;
		}
		if(!$this->backgroundColor){
			$this->backgroundColor = $class::COLOR;
		}
	}
	/**
	 * @return BaseModel
	 */
	public function getModel(): BaseModel{
		return $this->model;
	}
	/**
	 * @return BaseModel
	 */
	public function getClass(): string{
		return get_class($this->getModel());
	}
	public function getImage(): string{
		return $this->image = $this->getModel()->getImage();
	}
	public function getTooltip(): string{
		return $this->tooltip = $this->getModel()->getSubtitleAttribute();
	}
	public function getFontAwesome(): string{
		return $this->fontAwesome = $this->getModel()->getFontAwesome();
	}
	public function getTitleAttribute(): string{
		return $this->title = $this->getModel()->getTitleAttribute();
	}
	public function getBackgroundColor(): string{
		return $this->backgroundColor = $this->getModel()->getColor();
	}
}
