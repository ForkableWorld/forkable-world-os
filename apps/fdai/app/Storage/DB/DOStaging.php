<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage\DB;
use App\Models\User;
class DOStaging extends QMDB
{
    public const CONNECTION_NAME = 'do-staging';
    public const DB_NAME = 'qm_staging';
    public const DB_HOST_PUBLIC = '134.209.130.63';
    public const DB_HOST_PRIVATE = null;
    public const DB_PORT = 3306;
	public static function getConnectionName(): string{return static::CONNECTION_NAME;}
protected static function getDBDriverName():string{return 'mysql';}
	public static function getDefaultDBName(): string{return static::DB_NAME;}
    public static function copyDBToRDS(){
        QMDB::copyDB(DOStaging::class, StagingDB::class);
    }
    public static function copyTableToRDS(){
        QMDB::copyTable(DOStaging::class, StagingDB::class, User::TABLE);
    }
    public static function copyDBToLightSailWeb(){
        QMDB::copyDB(DOStaging::class, StagingDB::class);
    }
}
