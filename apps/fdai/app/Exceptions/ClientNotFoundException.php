<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Models\Application;
use Exception;
class ClientNotFoundException extends Exception {
    /**
     * NotFoundException constructor.
     * @param string $clientId
     */
    public function __construct(string $clientId = null){
		if(!$clientId){
			$message = "Client ID not provided";
		} else {
			$message = "Client with id $clientId not found! ";
		}
        parent::__construct($message . Application::getBuilderLinkSentence(), QMException::CODE_NOT_FOUND);
    }
}
