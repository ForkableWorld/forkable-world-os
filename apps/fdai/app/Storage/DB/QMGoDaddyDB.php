<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage\DB;
class QMGoDaddyDB extends QMDB
{
    public const CONNECTION_NAME = 'godaddy';
    public const DB_NAME = 'i5114137_wp9';
    public const DB_HOST_PUBLIC = '107.180.41.239';
    public const DB_HOST_PRIVATE = null;
    public const DB_PORT = 3306;
	public static function getConnectionName(): string{return static::CONNECTION_NAME;}
	public static function getDefaultDBName(): string{return static::DB_NAME;}
    public static function copyToGodaddy(){
        QMDB::copyWPTables(Writable::class, QMGoDaddyDB::class);
    }
	protected static function getDBDriverName():string{return 'mysql';}
}
