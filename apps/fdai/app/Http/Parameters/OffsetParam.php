<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Parameters;
use App\Slim\View\Request\QMRequest;
class OffsetParam extends BaseParam {
	public static function getSynonyms(): array{
		return ['offset'];
	}
	/**
	 * @param int $default
	 * @return int
	 */
	public static function getOffset(int $default = 0){
		$value = QMRequest::getParam(['offset'], $default, false);
		if($value !== null){
			$value = (int)$value;
		}
		return $value;
	}
}
