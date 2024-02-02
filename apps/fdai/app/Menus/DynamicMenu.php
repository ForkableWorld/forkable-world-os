<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Menus;
use App\Buttons\QMButton;
use App\Traits\HasButton;
class DynamicMenu extends QMMenu {
	public function getTitleAttribute(): string{
		if(!$this->title){
			le("Please set title on ", $this);
		}
		return $this->title;
	}
	public function getImage(): string{ return $this->image; }
	public function getFontAwesome(): string{ return $this->fontAwesome; }
	public function getTooltip(): string{ return $this->tooltip; }
	/**
	 * @return QMButton[]
	 */
	public function getButtons(): array{ return $this->buttons; }
	/**
	 * @param HasButton[]|QMButton[] $objects
	 * @return QMMenu
	 */
	public static function toMenu(array $objects): QMMenu{
		$menu = new static();
		foreach($objects as $object){
			if($object instanceof QMButton){
				$menu->buttons[] = $object;
			} else{
				$menu->buttons[] = $object->getButton();
			}
		}
		return $menu;
	}
	public function setButtons(array $buttons): DynamicMenu{
		$this->buttons = $buttons;
		return $this;
	}
}
