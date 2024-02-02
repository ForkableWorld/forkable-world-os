<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Menus\Astral;
use App\Buttons\QMButton;
use App\UI\FontAwesome;
use App\UI\QMColor;
class AstralRelationshipMenu extends SingleModelAstralMenu {
	public $title = ""; // Leave empty for more room in tables "Related Data";
	public $fontAwesome = FontAwesome::RELATIONSHIPS;
	public $backgroundColor = QMColor::HEX_BLUE;
	public $tooltip = "See related data";
	/**
	 * @param null $tableOrModel
	 */
	public function __construct($tableOrModel = null){
		parent::__construct($tableOrModel);
	}
	/**
	 * @return QMButton[]
	 */
	public function getButtons(): array{
		if($buttons = $this->buttons){
			return $buttons;
		}
		$m = $this->getModel();
		$this->addButtons($m->getInterestingRelationshipButtons());
		return $this->buttons;
	}
}
