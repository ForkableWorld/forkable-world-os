<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\AppSettings\AppSettings;
use Exception;
class InvalidClientException extends Exception {
    /**
     * NotFoundException constructor.
     */
    public function __construct(string $message = ""){
        $str = "Please provide your client_id from " . AppSettings::APP_BUILDER_URL . ". ";
        if(empty($message)){
            $message = $str;
        } else {
            $message .= "\n" . $message;
        }
        parent::__construct($message, 401);
    }
}
