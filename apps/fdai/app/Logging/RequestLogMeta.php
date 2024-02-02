<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Logging;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\View\Request\QMRequest;
use App\Types\QMArr;
class RequestLogMeta {
	public static function get():array{
		$QMRequest = qm_request();
		$arr = [
			'REQUEST_PARAMS' => $QMRequest->input() + $QMRequest->query(),
			'REQUEST_PATH' => QMRequest::requestUri(),
			'CLIENT_ID' => BaseClientIdProperty::fromMemory(),
		];
		return QMArr::notEmptyValues($arr);
	}
}
