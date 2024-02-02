<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Utils;
class EnvOverride extends Env {
	/**
	 * @param string $key
	 * @return mixed|null
	 */
	public static function getFormatted(string $key): mixed{
		// Uncomment for debugging logging issues QMLogger::cli()->info(__METHOD__." calling getEnvVariablesFromFile...");
		$variables = self::getEnvVariablesFromFile(".env.override");
		$val = $variables[$key] ?? null;
		return self::formatValue($val);
	}
}
