<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons;
use App\Http\Controllers\ButtonActionController;
abstract class RunnableButton extends QMButton {
	public function __construct(array $params){
		parent::__construct();
		$this->link = ButtonActionController::generateUrl(static::class, $params);
	}
	abstract public function run(array $parameters = []);
}
