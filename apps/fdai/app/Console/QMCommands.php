<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Console;
/**
 * @package App\Console
 * TODO: search for usages of Kernel::artisan and add functions all commands here
 */
class QMCommands extends Kernel {
    const CLEAR_CACHE = 'cache:clear';
	public static function pruneTelescopeTable(){
		Kernel::artisan("telescope:prune", ["--hours" => 1]);
	}
    public static function clearCache(){
        Kernel::artisan(self::CLEAR_CACHE);
    }

}
