<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Exceptions\BlackListedStringException;
use App\Types\QMStr;
class SecretException extends BlackListedStringException
{
    /**
     * @var string
     */
    private $blacklisted;
    /**
     * @var string
     */
    private $truncatedSecret;
    /**
     * InvalidStringException constructor.
     * @param string $secret
     * @param string $invalidString
     * @param string $type
     * @param string|null $message
     */
    public function __construct(string $secret, string $invalidString, string $type, string $message = null){
        $this->blacklisted = $secret;
        $this->fullString = $invalidString;
        $this->truncatedSecret = QMStr::truncate($secret, 4);
        parent::__construct("$message$type should not contain $this->truncatedSecret", $invalidString, $type);
    }
    public function getInvalidStringSegment(): string {
        $str = QMStr::getSurrounding($this->blacklisted, $this->fullString);
        $obfuscatedStr = str_replace($this->blacklisted, $this->truncatedSecret, $str);
        return $obfuscatedStr;
    }
}
