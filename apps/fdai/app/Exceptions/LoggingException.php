<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use Throwable;
class LoggingException extends \Exception{
	public function __construct(Throwable $loggingException, string $whatWeTriedToLog, Throwable $previous = null){
		parent::__construct("Could not use ExceptionLogMessage for:\n\t$whatWeTriedToLog
		Due to this LOGGING PROBLEM: 
			".$loggingException->getMessage(), 500, $previous);
	}
}
