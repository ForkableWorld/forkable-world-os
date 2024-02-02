<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage\DB;
use App\Slim\View\Request\QMRequest;
class Adminer
{
    public static function getAdminerUrl(array $params = []):string{
        $params = array_merge($params, self::getParams());
        return QMRequest::getAppHostUrl('admin/adminer', $params);
    }
    public static function getTableStructureUrl(string $table):string{
        return self::getAdminerUrl(['table' => $table]);
    }
    public static function getTableSelectUrl(string $table):string{
        return self::getAdminerUrl(['select' => $table]);
    }
    public static function getStatementUrl(string $sql):string{
        return self::getAdminerUrl(['sql' => $sql]);
    }
	/**
	 * @param string $table
	 * @param $id
	 * @return string
	 */
	public static function getRecordEditUrl(string $table, $id):string{
        return self::getAdminerUrl(['edit' => "$table&where%5Bid%5D=$id"]);
    }
	/**
	 * @param string $table
	 * @param string $field
	 * @param $id
	 * @return string
	 */
	public static function getSetNullUrl(string $table, string $field, $id): string{
        return self::getStatementUrl("update $table set $field = null where id = $id");
    }
    public static function getParams(): array{
        return [
            'server'   => Writable::getHost(), // Not sure what this was for? ServerHelper::getCurrentServerExternalIp(),
            'username' => Writable::getUser(),
            // Don't include password because it's insecure and adminer forces manual entry anyway
            // 'password' => Writable::getPassword(),
            'db'       => Writable::getDbName(),
        ];
    }
	/**
	 * @param string $sql
	 * @return string
	 */
	public static function getUrl(string $sql): string{
        return self::getStatementUrl($sql);
    }
}
