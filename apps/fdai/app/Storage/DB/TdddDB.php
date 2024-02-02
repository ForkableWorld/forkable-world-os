<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage\DB;
class TdddDB extends QMDB
{
    public const CONNECTION_NAME = 'tddd';
    public const DB_NAME = 'tddd';
    public const DB_HOST_PUBLIC = 'r5-large-cluster.cluster-corrh0fp2kuj.us-east-1.rds.amazonaws.com';
    public const DB_HOST_PRIVATE = null;
    public const DB_PORT = 3306;
	public static function getConnectionName(): string{return static::CONNECTION_NAME;}
protected static function getDBDriverName():string{return 'mysql';}
	public static function getDefaultDBName(): string{return static::DB_NAME;}
}
