<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use Throwable;
class NoGeoDataException extends \Exception {
    /**
     * NoGeoDataException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "No geo coordinates!", $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
