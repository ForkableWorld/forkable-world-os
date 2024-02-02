<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
class TemporaryConnectionException extends QMException {
    /**
     * @param string $connectorDisplayName
     * @param array|null $messageParams
     * @param Throwable $previous
     */
    public function __construct(string $connectorDisplayName, array $messageParams = null, Throwable $previous = null){
        $m = $connectorDisplayName." is temporarily unavailable. Please Try again tomorrow.  Thanks!";
        parent::__construct(Response::HTTP_SERVICE_UNAVAILABLE, $m, $messageParams, $previous);
    }
}
