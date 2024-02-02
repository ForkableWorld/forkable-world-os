<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use Throwable;

class InvalidTimestampException extends BadRequestException {
    /**
     * @param string $message
     * @param array|null $messageParams
     * @param Throwable $previous
     */
    public function __construct(string $message, array $messageParams = null, Throwable $previous = null){
        parent::__construct($message, $messageParams, $previous);
    }
}
