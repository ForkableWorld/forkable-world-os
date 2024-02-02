<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DevOps;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
class XDebug {
	public static function disable(){
		$warning = "Note that you won't get arguments with your stack traces.";
		if(function_exists("xdebug_disable")){
			ConsoleLog::info("Disabling XDEBUG because we're running on Jenkins. $warning");
			xdebug_disable();
		} else{
			QMLog::once("xdebug does not appear to be enabled. $warning");
		}
	}
	public static function active(): bool{
        $exists = function_exists("xdebug_is_debugger_active");
        if($exists){
            return xdebug_is_debugger_active();
        }
        return false;
	}
	public static function break(){
		if(self::active()){
			xdebug_break();
		}
	}
}
