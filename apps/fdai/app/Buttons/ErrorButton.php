<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons;
class ErrorButton extends QMButton {
	/**
	 * ErrorCard constructor.
	 * @param string $title
	 * @param string $message
	 * @param array $buttons
	 */
	public function __construct(string $title, string $message, array $buttons){
		parent::__construct();
		$this->setTextAndTitle($title);
		$this->subtitle = "Warning";
		$this->setTooltip($message);
	}
}
