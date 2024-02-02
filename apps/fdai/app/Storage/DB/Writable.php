<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage\DB;
use App\Utils\AppMode;
use App\Utils\Env;
class Writable extends QMDB {
    public static function copyToStaging(){
        $qmDB = static::db();
        $wpTables = $qmDB->getBaseModelTableNames();
        foreach($wpTables as $table){
            $db = Writable::getBuilderByTable($table);
            $path = $db->dumpTable();
            static::importTableFromJson($path);
        }
    }
	public static function getConnectionName(): string{
		$var = Env::get('DB_CONNECTION');
		if(!$var){
			Env::loadEnvIfNoAppUrl();
			$var = Env::get('DB_CONNECTION');
			if(!$var){
				if(AppMode::isStagingUnitTesting()){
					le("Use DOPPLER_TOKEN or download the .env.staging-remote file from 
					https://dashboard.doppler.com/workplace/36d12cbffd16d990042f/projects/cd-api/configs/stg ");
				}
				le("Could not get DB_CONNECTION from .env file");
			}
		}
		return $var;
	}
	public static function getDefaultDBName(): string{return Env::get('DB_DATABASE');}

    public static function now()
    {
		if(static::isSQLite()){
			$val = static::selectStatic("SELECT datetime('now') as datetime");
			return $val[0]->datetime;
		}
		$val = static::selectStatic("SELECT NOW() as datetime");
		return $val[0]->datetime;
    }
	public static function assertDBTimeMatchesPHPTime(){
		$phpTime = date('Y-m-d H:i:s');
		$dbTime = static::now();
		$phpTime = strtotime($phpTime);
		$dbTime = strtotime($dbTime);
		$diff = $phpTime - $dbTime;
		$diff = abs($diff);
		$diff = $diff / 60;
		$diff = round($diff);
		if($diff > 1){
			le("DB time is off by $diff minutes");
		}
	}

    protected static function getDBDriverName(): string{
		return Env::get('DB_DRIVER');
	}
}
