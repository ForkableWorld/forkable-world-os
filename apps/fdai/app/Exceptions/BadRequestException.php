<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Utils\UrlHelper;
use Throwable;
class BadRequestException extends QMException {
    /**
     * @param string $message
     * @param array|null $messageParams
     * @param Throwable|null $previous
     */
    public function __construct(string $message, array $messageParams = null, Throwable $previous = null){
        //Memory::setFailedRequestErrorMessage($message);
        $message .= "\n See ".UrlHelper::API_DOCS_URL." for more information.";
        parent::__construct(self::CODE_BAD_REQUEST, $message, $messageParams, $previous);
    }
}
