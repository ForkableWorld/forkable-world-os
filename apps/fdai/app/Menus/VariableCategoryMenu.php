<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Menus;
use App\Buttons\QMButton;
use App\Models\VariableCategory;
class VariableCategoryMenu extends QMMenu {
	public function getTitleAttribute(): string{
		return "Variable Categories";
	}
	public function getImage(): string{
		return VariableCategory::DEFAULT_IMAGE;
	}
	public function getFontAwesome(): string{
		return VariableCategory::FONT_AWESOME;
	}
	public function getTooltip(): string{
		return VariableCategory::CLASS_DESCRIPTION;
	}
	/**
	 * @return QMButton[]
	 */
	public function getButtons(): array{
		if($this->buttons){
			return $this->buttons;
		}
		$buttons = (new VariableCategory)->getButtons();
		$this->addButtons($buttons);
		return $this->buttons;
	}
}
