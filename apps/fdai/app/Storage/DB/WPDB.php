<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage\DB;
class WPDB extends QMDB {
    public const CONNECTION_NAME = 'wp-prefixed';
	public static function getConnectionName(): string{return static::CONNECTION_NAME;}
protected static function getDBDriverName():string{return 'mysql';}
	public static function getDefaultDBName(): string{return static::DB_NAME;}
}
