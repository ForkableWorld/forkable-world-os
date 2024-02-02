<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage\DB;
use App\Logging\QMLog;
use App\Models\WpPost;
use App\Types\QMArr;
class TBNGoDaddyDB extends AbstractWP
{
    public const CONNECTION_NAME = 'tbn-godaddy';
    public const DB_NAME = 'i5114137_wp11';
    public const DB_HOST_PUBLIC = '107.180.41.239';
    public const DB_HOST_PRIVATE = null;
    public const DB_PORT = 3306;
    public static function copyToGodaddy(string $table, string $column){
        $fromGD = TBNGoDaddyDB::getBuilderByTable($table)->get();
        $GDByType = QMArr::indexBy($fromGD->all(), WpPost::FIELD_POST_TYPE);
        $fromDO = TBNDigitalOceanDB::getBuilderByTable($table)->get();
        $DOByType = QMArr::indexBy($fromDO->all(), WpPost::FIELD_POST_TYPE);
        $diffGD = $fromGD->diff($fromDO->all());
        $diffDO = $fromDO->diff($fromGD->all());
        QMLog::table($diffDO);
    }
}
