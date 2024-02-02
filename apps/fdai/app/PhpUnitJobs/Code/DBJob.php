<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\PhpUnitJobs\Code;
use App\CodeGenerators\Swagger\SwaggerJson;
use App\DataSources\QMClient;
use App\Files\PHP\ConstantGenerator;
use App\Logging\QMLog;
use App\Models\GlobalVariableRelationship;
use App\Models\Application;
use App\Models\Connection;
use App\Models\Connector;
use App\Models\UserVariableRelationship;
use App\Models\CtConditionTreatment;
use App\Models\Measurement;
use App\Models\SentEmail;
use App\Models\Study;
use App\Models\User;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Models\VariableCategory;
use App\Models\WpPost;
use App\PhpUnitJobs\JobTestCase;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Slim\Model\QMUnit;
use App\Storage\DB\BackupDB;
use App\Storage\DB\DOStaging;
use App\Storage\DB\Migrations;
use App\Storage\DB\ProductionDB;
use App\Storage\DB\QMDB;
use App\Storage\DB\StagingDB;
use App\Storage\DB\Writable;
use App\Variables\CommonVariables\EnvironmentCommonVariables\TimeBetweenSunriseAndSunsetCommonVariable;
use App\Variables\QMCommonVariable;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\CodeGenerators\ModelGenerator\Coders\Model\Rules\RulesGenerator;
class DBJob extends JobTestCase {
	/**
	 * @param string $statement
	 */
	public static function generateTryMigrationStatement(string $statement){
		\App\Logging\ConsoleLog::info("
        try {
            \App\Storage\DB\Writable::statementStatic(\"$statement\");
        } catch (\Throwable \$e){
            if(stripos(\$e->getMessage(), 'exists') === false){
                throw \$e;
            }
        }
        ");
	}
	public function generateCommentMigrationsFromSwagger(){
		$paths = SwaggerJson::paths();
		foreach($paths as $path){
			\App\Logging\ConsoleLog::info($path->description);
			\App\Logging\ConsoleLog::info($path->description);
		}
	}
	public function testCT(){
		CtConditionTreatment::populateTable();
	}
	public function testCopyProductionToStaging(){
		// For Debugging ProductionDB::copyTableToStaging("action_events");
		ProductionDB::copyTablesToStaging();
	}
	public function testCopyToStaging(){
		DOStaging::copyDBToRDS();
	}
	public function testCopyToStagingWeb(){
		StagingDB::importFromFolder('storage/db/do-staging');
	}
	public function testGenerateCommentsFromSwagger(){
		SwaggerJson::addCommentsToDB();
	}
	public function testGenerateConstants(){
		foreach(QMDB::$mappings as $mysqlTypes){
			ConstantGenerator::dumpConstants($mysqlTypes);
		}
	}
	public function testGenerateMigrationForCauseEffect(){
		$tables = Writable::getAllTablesWithColumnLike('cause_variable_id');
		foreach($tables as $t){
			if(stripos($t, 'deleted_') === 0){
				continue;
			}
			if(stripos($t, 'ct_') === 0){
				continue;
			}
			if(stripos($t, 'crypto_') === 0){
				continue;
			}
			if(stripos($t, 'wp_') === 0){
				continue;
			}
			Migrations::makeMigration($t."_cause_variable_id","alter table $t add cause_variable_id int(10) unsigned;",
				"alter table $t add cause_variable_id int(10) unsigned;");
			Migrations::makeMigration($t."_cause_variable_id","alter table $t add effect_variable_id int(10) unsigned;",
				"alter table $t add effect_variable_id int(10) unsigned;");
		}
	}
	public function testGenerateMigrationForClientForeignKeys(){
		$column = QMClient::FIELD_CLIENT_ID;
		$sourceTable = QMClient::TABLE;
		$tables = Writable::getTableNamesWithColumn($column);
		$keys = [];
		foreach($tables as $t){
			if(stripos($t, 'deleted_') === 0){
				continue;
			}
			if(stripos($t, 'ct_') === 0){
				continue;
			}
			if(stripos($t, 'crypto_') === 0){
				continue;
			}
			if(stripos($t, 'wp_') === 0){
				continue;
			}
			$keyName = $t . "_" . $sourceTable . "_" . $column . "_fk";
			if(in_array($keyName, $keys)){
				le("$keyName");
			}
			$keys[] = $keyName;
			Migrations::makeMigration($keyName."_".$column, "alter table $t
                add constraint " . $keyName . "
                    foreign key ($column) references $sourceTable ($column);", "alter table $t
                add constraint " . $keyName . "
                    foreign key ($column) references $sourceTable ($column);");
		}
	}
	public function testGenerateMigrationForDateColumns(){
		$tables = Writable::getAllTablesWithColumnLike('_time');
		foreach($tables as $t){
			if(stripos($t, 'deleted_') === 0){
				continue;
			}
			if(stripos($t, 'ct_') === 0){
				continue;
			}
			if(stripos($t, 'crypto_') === 0){
				continue;
			}
			if(stripos($t, 'wp_') === 0){
				continue;
			}
			$columns = Writable::getAllColumnsForTable($t);
			foreach($columns as $column){
				if(stripos($column, '_time') === false){
					continue;
				}
				$atColumn = str_replace('_time', '_at', $column);
				Migrations::makeMigration($atColumn, "alter table $t add $atColumn timestamp null;",
					"alter table $t add $atColumn timestamp null;");
			}
		}
	}
	public function testGenerateMigrationForUnusedRedundantIndexes(){
		//Writable::getUnusedIndexes();
		Writable::getRedundantIndexes();
	}
	public function testGenerateMigrationForUserForeignKeys(){
		$needle = 'user_id';
		$tables = Writable::getAllTablesWithColumnLike('user_id');
		$octoberTables = Arr::where($tables, function($t){
			return $this->tableIsOctoberOrWP($t);
		});
		$keys = [];
		foreach($octoberTables as $t){
			$columns = Writable::getColumnsLike($t, $needle);
			foreach($columns as $column){
				$keyName = $t . "_wp_users_ID_fk";
				if(count($columns) > 1){
					$keyName = $t . "_" . $column . "_wp_users_ID_fk";
				}
				if(in_array($keyName, $keys)){
					le("$keyName");
				}
				$keys[] = $keyName;
				Migrations::makeMigration("foreign_keys_$t"."_one", "alter table $t
                    modify $column bigint unsigned not null;", "alter table $t
                    modify $column bigint unsigned not null;");
				Migrations::makeMigration("foreign_keys_$t"."_two", "alter table $t
                add constraint " . $keyName . "
                    foreign key ($column) references wp_users (ID);", "alter table $t
                add constraint " . $keyName . "
                    foreign key ($column) references wp_users (ID);");
			}
		}
	}
	public function testGenerateMigrationToDropForeignKeysForDeletedTables(){
		$tables = Writable::getAllTableNamesLike('deleted_');
		foreach($tables as $t){
			$keys = Writable::getForeignKeysForTable($t);
			foreach($keys as $keyData){
				$keyName = $keyData->CONSTRAINT_NAME;
				$isForeign = strpos($keyName, '_fk') !== false;
				if(!$isForeign){
					continue;
				}
				Migrations::makeMigration($keyName, "alter table $t drop foreign key $keyName;",
					"alter table $t drop foreign key $keyName;");
			}
		}
	}
	/**
	 * @throws DBALException
	 * @throws Exception
	 */
	public function testGenerateRules(){
		$sm = DB::connection()->getDoctrineSchemaManager();
		$sm->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
		$rules = RulesGenerator::make($sm)->getAllTableRules();
		QMLog::export($rules);
	}
	public function testGenerateVariableCategoryIdForeignKeys(){
		self::generateForeignKeyMigrations(Variable::TABLE);
		//self::generateForeignKeyMigrations(VariableCategory::TABLE);
		//self::generateForeignKeyMigrations(Unit::TABLE);
	}
	/**
	 * @param string $referencedTableName
	 * @param string $referencedIdColumnName
	 */
	public static function generateForeignKeyMigrations(string $referencedTableName,
		string $referencedIdColumnName = "id"){
		$r =
			Writable::alreadyHasForeignKey(Measurement::TABLE, VariableCategory::TABLE, VariableCategory::FIELD_ID);
		$foreignColumnNameLike = Str::singular($referencedTableName) . "_" . $referencedIdColumnName;
		$foreignTables = Writable::getAllTablesWithColumnLike($foreignColumnNameLike);
		$keys = [];
		foreach($foreignTables as $foreignTable){
			if(Writable::alreadyHasForeignKey($foreignTable, $referencedTableName, $referencedIdColumnName)){
				continue;
			}
			$foreignColumns = Writable::getColumnsLike($foreignTable, $foreignColumnNameLike);
			foreach($foreignColumns as $foreignColumn){
				$keyName = $foreignTable . "_" . $foreignColumn . "_fk";
				if(in_array($keyName, $keys)){
					\App\Logging\ConsoleLog::info("Already have $keyName");
					continue;
				}
				$keys[] = $keyName;
				Migrations::makeMigration($keyName."_".$foreignTable, "alter table $foreignTable
                add constraint " . $keyName . "
                    foreign key ($foreignColumn) references $referencedTableName ($referencedIdColumnName);", "alter table $foreignTable
                add constraint " . $keyName . "
                    foreign key ($foreignColumn) references $referencedTableName ($referencedIdColumnName);");
			}
		}
	}
	public function testGenerateWpPostColumnMigrations(){
		$tables = [
			GlobalVariableRelationship::TABLE,
			Application::TABLE,
			Connection::TABLE,
			Connector::TABLE,
			UserVariableRelationship::TABLE,
			SentEmail::TABLE,
			Study::TABLE,
			User::TABLE,
			UserVariable::TABLE,
			Variable::TABLE,
			VariableCategory::TABLE,
		];
		self::generateAddForeignColumnMigrations(WpPost::TABLE, $tables, WpPost::FIELD_ID);
	}
	/**
	 * @param string $referencedTableName
	 * @param array $foreignTables
	 * @param string $referencedIdColumnName
	 */
	public static function generateAddForeignColumnMigrations(string $referencedTableName, array $foreignTables,
		string $referencedIdColumnName = "id"){
		$foreignColumnName = strtolower(Str::singular($referencedTableName) . "_" . $referencedIdColumnName);

		$type = Writable::getColumnType($referencedTableName, $referencedIdColumnName);
		$type = str_replace(" auto_increment", "", $type);
		foreach($foreignTables as $foreignTable){
			if(Writable::columnExists($foreignTable, $foreignColumnName)){
				continue;
			}
			$keyName = $foreignColumnName . "_fk";
			Migrations::makeMigration($keyName, "alter table $foreignTable add $foreignColumnName $type null;",
				"alter table $foreignTable add $foreignColumnName $type null;");
			Migrations::makeMigration($keyName."_reverse",
                "alter table $foreignTable
                    add constraint $foreignTable" . "_" . $referencedTableName . "_" . $referencedIdColumnName . "_fk
                        foreign key ($foreignColumnName) references $referencedTableName ($referencedIdColumnName);
            ", "
                alter table $foreignTable
                    add constraint $foreignTable" . "_" . $referencedTableName . "_" . $referencedIdColumnName . "_fk
                        foreign key ($foreignColumnName) references $referencedTableName ($referencedIdColumnName);
            ");
		}
	}
	/** @noinspection SqlResolve */
	public function testListIndexSizes(){
		Migrations::logSelectToTable("SELECT database_name, table_name, index_name,
            round(stat_value*@@innodb_page_size/1024/1024, 2) size_in_mb
            FROM mysql.innodb_index_stats
            WHERE stat_name = 'size' AND index_name != 'PRIMARY'
            ORDER BY 4 DESC;");
	}
	public function testLogTableSizes(){
		Writable::getViewNames();
		//QMDB::getTablesLargerThan(10);
		//QMDB::logTableSizes();
	}
	public function testMakeMigrationToDropColumnInAllTables(){
		Migrations::generateMigrationToDropColumnInAllTables('cause_variable_id');
		Migrations::generateMigrationToDropColumnInAllTables('effect_variable_id');
		Migrations::generateMigrationToDropColumnInAllTables('analyzed_at');
	}
	public function testMigrateProduction(){
		ProductionDB::migrate();
	}
	public function testMigrateStaging(){
		StagingDB::migrate();
	}
	public function testOutputDiskSpaceUsage(){
		Writable::outputDiskSpaceUsageByColumn();
	}
	public function testOutputSlowQueries(){
		Writable::outputSlowQueries();
	}
	/** @noinspection SqlResolve */
	public function testPruneTelescopeTable(){
		Migrations::logSelectToTable("SELECT database_name, table_name, index_name,
            round(stat_value*@@innodb_page_size/1024/1024, 2) size_in_mb
            FROM mysql.innodb_index_stats
            WHERE stat_name = 'size' AND index_name != 'PRIMARY'
            ORDER BY 4 DESC;");
	}
	public function testRestoreMissingMeasurements(){
		BackupDB::analyzeRestoredUserVariables();
	}
	public function testUpdateDbFromVariableConstants_ON_PRODUCTION(){
		QMUnit::updateDatabaseTableFromHardCodedConstants();
		QMCommonVariable::updateDatabaseTableFromHardCodedConstants();
		$this->assertEquals(BaseCombinationOperationProperty::COMBINATION_MEAN,
			Variable::find(TimeBetweenSunriseAndSunsetCommonVariable::ID)->combination_operation);
	}
	/**
	 * @param $t
	 * @return bool
	 */
	private function tableIsOctoberOrWP($t): bool{
		return strpos($t, 'o_') === 0 || strpos($t, 'wp_') === 0;
	}
}
