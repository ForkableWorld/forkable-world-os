<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage\DB;
use App\Logging\QMLog;
use App\Models\Measurement;
use App\Models\OAAccessToken;
use App\Models\OAAuthorizationCode;
use App\Models\OARefreshToken;
use App\Models\User;
use App\Models\UserVariableClient;
use App\Models\Vote;
use App\Models\WpPost;
use App\Properties\User\UserIdProperty;
class GlobalDataDB extends QMDB
{
    public const CONNECTION_NAME = 'cd_global';
    public const DB_NAME = 'cd_global';
    public const DB_HOST_PUBLIC = 'r5-large-cluster.cluster-corrh0fp2kuj.us-east-1.rds.amazonaws.com';
    public const DB_HOST_PRIVATE = null;
    public const DB_PORT = 3306;
	public static function getConnectionName(): string{return static::CONNECTION_NAME;}
protected static function getDBDriverName():string{return 'mysql';}
	public static function getDefaultDBName(): string{return static::DB_NAME;}
	public static function addPrimaryKeys(): void{
		$tables = static::getTablesByDescendingSize();
		foreach($tables as $table){
			if(!$table->hasPrimaryKey()){
				try {
					$table->addPrimaryKey();
				}catch (\Exception $e){
					QMLog::error(__METHOD__.": ".$e->getMessage());
				}
			}
		}
	}
	public static function snakize_field_names(){
		$tables = static::getDBTables();
		foreach($tables as $table){
			$table_name = $table->getName();
			$table_name_snakized = str_replace(' ', '_', $table_name);
			if($table_name_snakized != $table_name){
				$table->rename($table_name_snakized);
			}
			$table->snakize_column_names();
		}
	}
	public static function deleteNonPublicUserData(): void{
		static::findTableByName(WpPost::TABLE)->qb()
		      ->whereNotIn(WpPost::FIELD_POST_AUTHOR, UserIdProperty::getPublicUserIds())
		      ->delete();
		static::findTableByName(User::TABLE)->qb()
		      ->whereNotIn(User::FIELD_ID, UserIdProperty::getPublicUserIds())
		      ->delete();
		static::statementStatic("update oa_clients set user_id = 230 where user_id <> 230");
		static::findTableByName(Vote::TABLE)->deleteNonPublicUserData();
		static::findTableByName(UserVariableClient::TABLE)->deleteNonPublicUserData();
		static::findTableByName('variable_user_sources')->deleteNonPublicUserData();
		static::findTableByName(OAAccessToken::TABLE)->qb()->delete();
		static::findTableByName(OARefreshToken::TABLE)->qb()->delete();
		static::findTableByName(OAAuthorizationCode::TABLE)->qb()->delete();

		$tables = static::getTablesByDescendingSize();
		foreach($tables as $table){
			if($table->getName() === Measurement::TABLE){continue;}
			try{
				$table->deleteNonPublicUserData();
			}catch(\Exception $e) {
				QMLog::error(__METHOD__.": ".$e->getMessage());
			}
		}
		static::findTableByName(User::TABLE)->qb()
		      ->whereNotIn(User::FIELD_ID, UserIdProperty::getPublicUserIds())
		      ->delete();
	}
}
