<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Types\QMStr;
class BlackListedStringException extends InvalidStringException {
    /**
     * @var string
     */
    private $blacklisted;
    /**
     * InvalidStringException constructor.
     * @param string $blacklisted
     * @param string $invalidString
     * @param string $type
     * @param string|null $message
     */
    public function __construct(string $blacklisted, string $invalidString, string $type, string $message = null){
       $this->blacklisted = $blacklisted;
       parent::__construct("$message$type should not contain $blacklisted", $invalidString, $type);
    }
    public function getInvalidStringSegment(): string {
        return QMStr::getSurrounding($this->blacklisted, $this->fullString);
    }
}
