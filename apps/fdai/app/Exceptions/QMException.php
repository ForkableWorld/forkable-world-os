<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use RuntimeException;
use Throwable;

class QMException extends RuntimeException {
    /**
     * The request could not be understood by the server due to malformed syntax.
     * The client SHOULD NOT repeat the request without modifications.
     */
    public const CODE_BAD_REQUEST = 400;
    /**
     * The request requires user authentication.
     * If the request already included Authorization credentials, then the 401 response indicates
     * that authorization has been refused for those credentials.
     */
    public const CODE_UNAUTHORIZED = 401;
    /**
     * The server understood the request, but is refusing to fulfill it.
     * Authorization will not help and the request SHOULD NOT be repeated.
     */
    public const CODE_FORBIDDEN = 403;
    /**
     * The server has not found anything matching the Request-URI. No indication is given of whether the
     * condition is temporary or permanent.
     * This status code is commonly used when the server does not wish to reveal exactly why the
     * request has been refused, or when no other response is applicable.
     */
    public const CODE_NOT_FOUND = 404;
    /**
     * The server encountered an unexpected condition which prevented it from fulfilling the request.
     */
    public const CODE_INTERNAL_SERVER_ERROR = 500;
    /**
     * The server does not support the functionality required to fulfill the request.
     */
    public const CODE_NOT_IMPLEMENTED = 501;
    /**
     * The server does not support the functionality required to fulfill the request.
     */
    public const CODE_RATE_LIMIT_EXCEEDED = 529;
    public const NAME = 'QMException';
    /**
     * Construct the exception. Note: The message is NOT binary safe.
     * @link http://php.net/manual/en/exception.construct.php
     * @param int $responseCode The response code returned to the client, doubles as exception code.
     * @param string $message The Exception message to throw.
     * @param array $messageParams [optional] An array containing parameters for the message.
     * @param Throwable $previous [optional] The previous exception used for the exception chaining. Since 5.3.0
     * @param bool $logException
     */
    public function __construct($responseCode, $message, array $messageParams = null, Throwable $previous = null, bool $logException = true){
        $message = $this->formatMessage($message, $messageParams);
        parent::__construct($message, $responseCode, $previous);
    }
    /**
     * Format the message with the given parameters.
     * @param string $message The message that should be formatted.
     * @param array $messageParams An array containing any parameters that should be inserted into the message.
     * @return string The formatted message.
     */
    private function formatMessage($message, array $messageParams = null){
        if(!empty($messageParams)){
            return vsprintf($message, $messageParams);
        }
        return $message;
    }
}
