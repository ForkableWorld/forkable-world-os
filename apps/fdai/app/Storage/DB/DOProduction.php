<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage\DB;
class DOProduction extends QMDB
{
    public const CONNECTION_NAME = 'do-production';
    public const DB_NAME = 'qm_production';
    public const DB_HOST_PUBLIC = '167.172.15.169';
    public const DB_HOST_PRIVATE = null;
    public const DB_PORT = 3306;
	public static function getConnectionName(): string{return static::CONNECTION_NAME;}
protected static function getDBDriverName():string{return 'mysql';}
	public static function getDefaultDBName(): string{return static::DB_NAME;}
    public static function copyToRDS(){
        QMDB::copyDB(DOProduction::class, ProductionDB::class);
    }
}
