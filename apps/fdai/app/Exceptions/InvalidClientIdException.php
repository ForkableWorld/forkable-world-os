<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\AppSettings\AppSettings;
use App\Models\Application;
use RuntimeException;

class InvalidClientIdException extends RuntimeException {
    /**
     * NotFoundException constructor.
     * @param string $clientId
     * @param string $message
     */
    public function __construct(string $clientId = null, string $message = ''){
        if($clientId){
            $message = "$clientId is not a valid QuantiModo client_id parameter! $message ".
	            Application::getBuilderLinkSentence();
        } else {
            $message = "No QuantiModo client_id parameter provided! $message ". Application::getBuilderLinkSentence();
        }
        parent::__construct($message, QMException::CODE_BAD_REQUEST);
    }
}
