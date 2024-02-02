<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Logging;
use App\Override\QMErrorPageHandler;
use Facade\Ignition\Facades\Flare;
use Throwable;
class QMFlare {
	public static function getReport(Throwable $e = null): \Facade\FlareClient\Report{
		return QMErrorPageHandler::getReport($e);
	}
	/**
	 * @param string $name
	 * @param string $messageLevel
	 * @param array|null $meta
	 */
	public static function addGlow(string $name, string $messageLevel, array $meta = null): void{
		try {
			Flare::glow($name, $messageLevel, $meta);
		} catch (\Throwable $e) {
			error_log("Could not add glow:
	$messageLevel:
	$name			 
	because " . 
	$e->getMessage());
		}
	}
}
