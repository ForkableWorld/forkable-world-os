<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model;
use App\Utils\AppMode;
class AppEnvironment {
	/**
	 * @return bool
	 */
	public static function isCircleCIOrTravis(){
		if(!empty(\App\Utils\Env::get('TRAVIS'))){
			return true;
		}
		return (bool)\App\Utils\Env::get('CIRCLE_BUILD_NUM');
	}
	/**
	 * @return bool
	 */
	public static function isCircleCIOrTravisOrJenkins(): bool{
		return self::isCircleCIOrTravis() || AppMode::isJenkins();
	}
}
