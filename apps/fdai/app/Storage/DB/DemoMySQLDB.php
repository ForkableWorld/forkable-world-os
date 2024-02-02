<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage\DB;
use App\Models\GlobalVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Models\Measurement;
use App\Models\Study;
use App\Models\UserVariable;
use App\Models\UserVariableClient;
use App\Models\Variable;
use App\Models\Vote;
class DemoMySQLDB extends AbstractMySQLDB
{
    public const CONNECTION_NAME = 'demo-mysql-db';
    public const DB_NAME = 'qm_production';
    public const DB_HOST_PUBLIC = 'demo-db-cluster.cluster-corrh0fp2kuj.us-east-1.rds.amazonaws.com';
    public const DB_HOST_PRIVATE = null;
    public const DB_PORT = 3306;
	public static function getConnectionName(): string{return static::CONNECTION_NAME;}
	public static function getDefaultDBName(): string{return static::DB_NAME;}
	public static function getHost(): ?string{
		return static::DB_HOST_PUBLIC;
	}
	public static function createDemoDB(){
		static::disableForeignKeyConstraints(static::db());
        $tables = static::getDBTables();
		foreach($tables as $table){
			if($table->hasUserIdColumn()){
				$table->where($table->getUserIdColumn(), '<>', 230)->delete();
			}
		}
		$upVotes = Vote::whereUpVoted()->get();
	    $causeIds = $upVotes->pluck(Vote::FIELD_CAUSE_VARIABLE_ID)->toArray();
	    $causeIds = array_unique($causeIds);
		$effectIds = $upVotes->pluck(Vote::FIELD_EFFECT_VARIABLE_ID)->toArray();
		$effectIds = array_unique($effectIds);
		$ids = array_merge($causeIds, $effectIds);
		$ids = array_unique($ids);
		GlobalVariableRelationship::whereNotIn(GlobalVariableRelationship::FIELD_CAUSE_VARIABLE_ID, $causeIds)
			->whereNotIn(GlobalVariableRelationship::FIELD_EFFECT_VARIABLE_ID, $effectIds)
			->delete();
	    UserVariableRelationship::whereNotIn(UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID, $causeIds)
		    ->whereNotIn(UserVariableRelationship::FIELD_EFFECT_VARIABLE_ID, $effectIds)
		    ->delete();
	    Study::whereNotIn(UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID, $causeIds)
		    ->whereNotIn(UserVariableRelationship::FIELD_EFFECT_VARIABLE_ID, $effectIds)
		    ->delete();
		//Measurement::whereNotIn(Measurement::FIELD_VARIABLE_ID, $ids)->delete();
	    //UserVariable::whereNotIn(UserVariable::FIELD_VARIABLE_ID, $ids)->delete();
		//UserVariableClient::whereNotIn(UserVariableClient::FIELD_VARIABLE_ID, $ids)->delete();
		Variable::whereNotIn(Variable::FIELD_ID, $ids)->delete();
	    static::enableForeignKeyConstraints(static::db());
    }
}
