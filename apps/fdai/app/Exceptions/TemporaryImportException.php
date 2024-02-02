<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\DataSources\QMConnector;
use Symfony\Component\HttpFoundation\Response;
class TemporaryImportException extends QMException {
	/**
	 * @param QMConnector $c
	 * @param string $internalInternalMessage
	 */
	public function __construct(QMConnector $c, string $internalInternalMessage){
		$userMessage = $c->displayName . " is temporarily unable to import. Please Try again tomorrow.  Thanks!";
		$c->getOrCreateConnection()->setImportErrorMessage($userMessage, $internalInternalMessage);
		parent::__construct(Response::HTTP_SERVICE_UNAVAILABLE, $userMessage);
	}
}
