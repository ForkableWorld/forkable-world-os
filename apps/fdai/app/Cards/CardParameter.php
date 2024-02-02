<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Cards;
use App\InputFields\InputField;
class CardParameter extends InputField {
	/**
	 * CardParameter constructor.
	 * @param $key
	 * @param null $value
	 */
	public function __construct($key, $value = null){
		parent::__construct($key, $key, $value);
		$this->setShow(false);
	}
}
