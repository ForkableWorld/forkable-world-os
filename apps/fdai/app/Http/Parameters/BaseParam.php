<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Parameters;
use App\Slim\View\Request\QMRequest;
abstract class BaseParam {
	/**
	 * @return string[]
	 */
	abstract public static function getSynonyms(): array;
	/**
	 * @return null|string
	 */
	public static function get(bool $throwException = false, $default = null){
		return QMRequest::getParam(static::getSynonyms(), $default, $throwException);
	}
}
