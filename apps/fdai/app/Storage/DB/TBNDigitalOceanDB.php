<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage\DB;
class TBNDigitalOceanDB extends AbstractWP
{
    public const CONNECTION_NAME = 'tbn-do';
    public const DB_NAME = 'wordpress';
    public const DB_HOST_PUBLIC = '159.65.239.236';
    public const DB_HOST_PRIVATE = null;
    public const DB_PORT = 3306;
    public static function copyToGodaddy(){
        QMDB::copyDB(TBNDigitalOceanDB::class, QMGoDaddyDB::class);
    }
    public static function goDaddyToDO(){
        QMDB::copyDB(QMGoDaddyDB::class, TBNDigitalOceanDB::class);
    }
}
