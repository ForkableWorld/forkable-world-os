<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Mail;
use App\Types\TimeHelper;
class TooManyEmailsException extends \Exception {
	/**
	 * @param string $to
	 * @param string $type
	 * @param string $lastAt
	 */
	public function __construct(string $to, string $type, string $lastAt){
		$message = "Not sending $type email to $to because we already sent $type to them " .
			TimeHelper::timeSinceHumanString($lastAt);
		parent::__construct($message);
	}
}
