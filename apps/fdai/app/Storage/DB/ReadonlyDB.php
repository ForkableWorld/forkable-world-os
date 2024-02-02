<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage\DB;
class ReadonlyDB extends QMDB {
    public static function getConnectionName(): string{return \App\Utils\Env::get('DB_CONNECTION');}
    public static function getDefaultDBName(): string{return \App\Utils\Env::get('DB_DATABASE');}
	protected static function getDBDriverName(): string{
		return config('database.connections.'.self::getConnectionName().'.driver');
	}
}
