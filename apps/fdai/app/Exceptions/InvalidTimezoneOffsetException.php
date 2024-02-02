<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;


use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use Throwable;
class InvalidTimezoneOffsetException extends \Exception implements ProvidesSolution
{
    public function __construct($message = "", $code = 0, Throwable $previous = null){
        parent::__construct($message, $code, $previous);
    }
    public function getSolution(): Solution{
        // TODO: Implement getSolution() method.
    }
}
