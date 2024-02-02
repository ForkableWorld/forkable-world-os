<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLabIndex;
use App\Types\QMStr;
class LeastRecentIndexButtonAscendingIndexButton extends DataLabAscendingIndexButton {
	public function __construct($tableOrModel, string $field){
		parent::__construct($tableOrModel, $field);
		$m = $this->getModel();
		$title = "Least Recently " . QMStr::snakeToTitle(str_replace('_at', '', $field));
		$this->setTextAndTitle($title);
		$this->setTooltip("See $title " . $m->getPluralizedClassName());
	}
}
