<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Exceptions;
use App\Types\QMStr;
use Throwable;
class InvalidFilePathException extends \Exception {
    public const MAXIMUM_FILE_NAME_LENGTH = 246;
    public function __construct($message = "", $code = 0, Throwable $previous = null){
        parent::__construct($message, $code, $previous);
    }
    public static function truncateFromBeginning(string $str, $max = self::MAXIMUM_FILE_NAME_LENGTH): string {
        while(strlen($str) > $max){
            $str = QMStr::removeFirstCharacter($str);
        }
        return $str;
    }
}
