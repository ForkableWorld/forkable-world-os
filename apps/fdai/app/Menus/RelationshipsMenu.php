<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Menus;
use App\Models\BaseModel;
class RelationshipsMenu extends QMMenu {
	/**
	 * @var BaseModel
	 */
	private $model;
	/**
	 * RelationshipsMenu constructor.
	 * @param BaseModel $model
	 */
	public function __construct($model){
		$this->model = $model->l();
	}
	public function getTitleAttribute(): string{
		if($this->title){
			return $this->title;
		}
		return $this->getModel()->getTitleAttribute();
	}
	public function getImage(): string{ return $this->getModel()->getImage(); }
	public function getFontAwesome(): string{ return $this->getModel()->getFontAwesome(); }
	public function getTooltip(): string{ return $this->getModel()->getSubtitleAttribute(); }
	public function getButtons(): array{
		if($this->buttons){
			return $this->buttons;
		}
		$m = $this->getModel();
		$buttons = $m->getInterestingRelationshipButtons();
		$this->addButtons($buttons);
		return $this->buttons;
	}
	/**
	 * @return BaseModel
	 */
	public function getModel(): BaseModel{
		return $this->model;
	}
}
