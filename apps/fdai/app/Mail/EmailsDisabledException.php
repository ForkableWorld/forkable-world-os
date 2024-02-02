<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Mail;
use App\Exceptions\QMException;
class EmailsDisabledException extends QMException {
	/**
	 * @param $message
	 */
	public function __construct($message){
		parent::__construct(400, $message, [], null, false);
	}
}
