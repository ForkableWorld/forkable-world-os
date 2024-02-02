<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Urls;
use App\DataSources\QMClient;
use App\DataSources\QMConnector;
use App\Exceptions\ClientNotFoundException;
class FinishUrl extends AbstractUrl {
	/**
	 * @param string|null $errorMessage
	 * @return \Illuminate\Http\RedirectResponse
	 * @throws \App\Exceptions\UnauthorizedException
	 */
	public static function sendToFinishPage(string $errorMessage = null){
		$intendedUrl = IntendedUrl::get();
		$finishUrl = \App\Utils\Env::getAppUrl() . '/api/v1/connection/finish';
		$params = [];
		if($errorMessage){
			$params['error'] = json_encode($errorMessage);
		}
		return QMConnector::addParamsToUrlAndRedirect($finishUrl, 'sessionToken', $params);
	}
	protected function generatePath(): string{
		return '/api/v1/connection/finish';
	}
}
