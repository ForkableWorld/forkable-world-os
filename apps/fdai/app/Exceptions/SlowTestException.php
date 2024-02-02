<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Logging\QMClockwork;
use App\Solutions\ProfileSolution;
use Exception;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use Throwable;
class SlowTestException extends Exception implements ProvidesSolution
{
	/**
	 * Construct the exception. Note: The message is NOT binary safe.
	 * @link https://php.net/manual/en/exception.construct.php
	 * @param string $message [optional] The Exception message to throw.
	 * @param int $code [optional] The Exception code.
	 * @param null|Throwable $previous [optional] The previous throwable used for the exception chaining.
	 */
	public function __construct($message = "", $code = 0, Throwable $previous = null){
		QMClockwork::logSlowOperation($message);
		parent::__construct($message, $code, $previous);
	}
	public function getSolution(): Solution{
        return new ProfileSolution();
    }
}
