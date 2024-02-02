<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons;
class LinkButton extends QMButton {
	/**
	 * LinkButton constructor.
	 * @param int|string $text
	 * @param mixed $url
	 * @param string|null $tooltip
	 * @param string $target
	 */
	public function __construct(string $text, string $url, string $tooltip = null, string $target = '_self'){
		parent::__construct($text, $url, null, null);
		$this->target = $target;
		$this->tooltip = $tooltip;
	}
}
