<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Traits\ExceptionTraits\IsStringException;
use Facade\IgnitionContracts\ProvidesSolution;
class InvalidStringException extends \Exception implements ProvidesSolution {
    use IsStringException;
	/**
	 * InvalidStringException constructor.
	 * @param string $message
	 * @param string $invalidString
	 * @param string $type
	 */
    public function __construct(string $message, string $invalidString, string $type){
        $this->attributeName = $type;
        $this->fullString = $invalidString;
        $message .= "\nbut contains/is:\n".$this->getInvalidStringSegment();
        parent::__construct($message);
    }
}
