<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Parameters;
use App\Slim\View\Request\QMRequest;
use App\Storage\Memory;
class DebugRequestParam extends BaseParam {
	public static function getSynonyms(): array{
		return ['debug'];
	}
	/**
	 * @return bool
	 */
	public static function isDebug(): bool{
		$res = (bool)QMRequest::getParam(QMRequest::PARAM_DEBUG);
		if(!$res){ // Faster
			Memory::setRequestParam(QMRequest::PARAM_DEBUG, false);
		}
		return $res;
	}
}
