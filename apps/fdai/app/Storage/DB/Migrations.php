<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage\DB;
use App\Console\Kernel;
use App\Files\FileFinder;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Models\Button;
use App\Models\Card;
use App\Models\UserVariableRelationship;
use App\Models\CorrelationCausalityVote;
use App\Models\CorrelationUsefulnessVote;
use App\Models\Credential;
use App\Models\DeviceToken;
use App\Models\Measurement;
use App\Models\Phrase;
use App\Models\Purchase;
use App\Models\SourcePlatform;
use App\Models\ThirdPartyCorrelation;
use App\Models\TrackerLog;
use App\Models\TrackerSession;
use App\Models\User;
use App\Models\UserClient;
use App\Models\UserVariable;
use App\Models\UserVariableClient;
use App\Models\WpPost;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseCreatedAtProperty;
use App\Properties\Base\BaseDeletedAtProperty;
use App\Properties\Base\BaseIdProperty;
use App\Properties\Base\BaseUpdatedAtProperty;
use App\Properties\Base\BaseUserIdProperty;
use App\Types\QMStr;
use App\Slim\Model\User\QMUser;
class Migrations extends QMDB {
    public const CONNECTION_NAME = 'migrations';
	const GLOBAL_COLUMNS = [
		BaseUserIdProperty::NAME,
		BaseIdProperty::NAME,
		BaseCreatedAtProperty::NAME,
		BaseDeletedAtProperty::NAME,
		BaseClientIdProperty::NAME,
	];
	public const PATH = "database/migrations";
	/**
     * @return int
     */
    public static function getLastModifiedTime(): int{
        return FileHelper::getLastModifiedTimeInFolder(self::PATH);
    }
    public static function migrate(): void {
        Kernel::artisan('migrate', ["--force" => 1, "--database" => "migrations"]);
    }
    /**
     * @param string $column
     * @return string
     */
    public static function generateMigrationToDropColumnInAllTables(string $column): string{
        $tables = Writable::getTableNamesWithColumn($column);
        $str = '';
        foreach($tables as $table){
            $str .= self::generateMigrationToDropForeignKeysReferencingAColumn($table, $column);
            $str .= self::generateMigrationToDropForeignKeysWithColumnName($table, $column);
            $str .= "alter table $table drop column $column;\n";
        }
        return self::makeMigration("drop_" . $column . "_everywhere", $str);
    }
    /**
     * @param string|null $tableName
     * @param string|null $columnName
     * @param string $comment
     * @return string
     */
    public static function commentMigration(string $tableName,
                                            string $columnName,
                                            string $comment): ?string{
        $type = Writable::getColumnType($tableName, $columnName);
        if(!$type){
            return null;
        }
        $existingComment = Writable::getColumnComment($tableName, $columnName);
        if(!empty($existingComment)){
            \App\Logging\ConsoleLog::info("Existing $tableName $columnName comment: $existingComment");
            if($existingComment === $comment){
                \App\Logging\ConsoleLog::info("Comment not changed");
                return null;
            }
            if(strlen($existingComment) > strlen($comment)){
                \App\Logging\ConsoleLog::info("Skipping because existing comment:
                    $existingComment
                    is longer than new comment:
                    $comment");
                return null;
            }
        }
        $statement = "alter table $tableName modify $columnName $type
            comment '$comment';";
        Migrations::makeMigration($tableName . '_' . $columnName . '_comment', $statement, static::getConnectionStatically());
        return $statement;
    }
    /**
     * @param string $name
     * @param string $statements
     * @return string
     */
    public static function makeMigration(string $name, string $statements, string $connection = null): string{
		$CLASS = QMStr::toClassName($name);
	    $path = "database/migrations/".date('Y_m_d_His_') .strtolower($name).".php";
        FileHelper::write($path, '<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class '.$CLASS.' extends Migration{
    public function up(){
        '.$statements.'
    }
}');
        return $path;
    }
    /**
     * @param string $table
     * @param string $column
     * @param string $newName
     * @return string
     */
    public static function getRenameColumnStatement(string $table, string $column, string $newName): string{
        $type = Writable::getColumnType($table, $column);
        return "alter table $table change `$column` $newName $type;";
    }
    public static function replaceInColumnNames(string $search, string $replace): void{
        $tableColumns = Writable::getAllColumnsLike($search);
        $sql = '';
        foreach($tableColumns as $table => $columns){
            foreach($columns as $column){
                $newName = str_replace($search, $replace, $column);
                $sql .= Migrations::getRenameColumnStatement($table, $column, $newName);
            }
        }
    }
    public static function generateMigrationToDropForeignKeysWithKeyNameLike(string $needle): string{
        $tables = static::getTableNames();
        $stmt = "";
        foreach($tables as $t){
            $t = UserVariableRelationship::TABLE;
            $keys = collect(QMDB::getForeignKeysForTable($t));
            $capitalUserKey = $keys->filter(function($keyData) use($needle) {
                return stripos($keyData->CONSTRAINT_NAME, $needle) !== false;
            })->first();
            if(!$capitalUserKey){
                continue;
            }
            $stmt .= "alter table $t drop foreign key ".$capitalUserKey->CONSTRAINT_NAME.";\n";
        }
        return Migrations::makeMigration("drop_" . $needle . "_foreign_keys", $stmt);
    }
    public static function generateMigrationToDropForeignKeysWithColumnName(string $table,
                                                                            string $column): string{
        $stmt = "";
        $keys = collect(Writable::getForeignKeysForTable($table));
        $matches = $keys->filter(function($keyData) use($column) {
            return $keyData->COLUMN_NAME === $column && strpos($keyData->CONSTRAINT_NAME, "_fk") !== false;
        })->all();
        if($matches){
            foreach($matches as $item){
                $stmt .= "alter table $table drop foreign key ".$item->CONSTRAINT_NAME.";\n";
            }
        }
        return $stmt;
    }
    public static function generateMigrationToDropForeignKeysReferencingAColumn(string $table,
                                                                                string $column): string{
        $tables = Writable::getForeignKeysReferencingGivenTableColumn($table, $column);
        $stmt = "";
        foreach($tables as $item){
            $stmt .= "alter table $table drop foreign key ".$item->CONSTRAINT_NAME.";\n";
        }
        return $stmt;
    }
    public static function getUser(): ?string{
        return parent::getUser();
    }
    public static function getPassword(): string{
        return parent::getPassword();
    }
    public static function generateNumberOfCountMigrations(): string{
        $tables = static::getTableNames();
        $all = "";
        foreach($tables as $table){
            if(stripos($table, 'o_') === 0){
                continue;
            }
            if($table !== User::TABLE && stripos($table, 'wp_') === 0){
                continue;
            }
            $relationships = Writable::getForeignKeysForTable($table);
            $tableTitle = QMStr::tableToTitle($table);
            foreach($relationships as $relationship){
                if(!$relationship->REFERENCED_TABLE_NAME){continue;}
                $referencedTable = $relationship->REFERENCED_TABLE_NAME;
                $ignoredTables = [
                    Button::TABLE,
                    Card::TABLE,
                    CorrelationCausalityVote::TABLE,
                    CorrelationUsefulnessVote::TABLE,
                    Credential::TABLE,
                    DeviceToken::TABLE,
                    Phrase::TABLE,
                    Purchase::TABLE,
                    SourcePlatform::TABLE,
                    ThirdPartyCorrelation::TABLE,
                    TrackerLog::TABLE,
                    TrackerSession::TABLE,
                    WpPost::TABLE,
                    UserVariableClient::TABLE,
                    UserClient::TABLE,
                ];
                if(in_array($referencedTable, $ignoredTables)){continue;}
                if(in_array($table, $ignoredTables)){continue;}
                if(stripos($referencedTable, 'o_') === 0){continue;}
                if($referencedTable !== User::TABLE && stripos($referencedTable, 'wp_') === 0){continue;}
                $column = $relationship->COLUMN_NAME;
                if(in_array($column, [
                    Measurement::FIELD_ORIGINAL_UNIT_ID,
                    UserVariable::FIELD_LAST_UNIT_ID,
                    UserVariable::FIELD_LAST_ORIGINAL_UNIT_ID,
                ])){continue;}
                try {
                    $primaryKey = Writable::getPrimaryKey($table);
                } catch (\Throwable $e) {
                    QMLog::info(__METHOD__.": ".$e->getMessage());
                    continue;
                }
                $referencedColumn = $relationship->REFERENCED_COLUMN_NAME;
                $foreignModel = QMStr::snakeToTitle(str_replace("_id", " ", $column));
                $newColumnsByTable[$table] = $relationship;
                $countField = "number_of_$table";
                if($relationship->COLUMN_NAME !== QMStr::singularize($referencedTable).'_id'){
                    $suffix = '_where_'.str_replace('_id', '', $relationship->COLUMN_NAME);
                    if($suffix !== "_where_client" && $suffix !== "_where_user"){
                        $countField .= $suffix;
                    }
                }
                $countField = str_replace("bshaffer_oauth", "oauth", $countField);
                $countField = str_replace("_wp_", "_", $countField);
                if(stripos($countField, "number_of_correlations_where_effect_") !== false){
                    $countField = "number_of_predictor_case_studies";
                }
                if(stripos($countField, "number_of_correlations_where_cause_") !== false){
                    $countField = "number_of_outcome_case_studies";
                }
                if(stripos($countField, "number_of_global_variable_relationships_where_cause_") !== false){
                    $countField = "number_of_outcome_population_studies";
                }
                if(stripos($countField, "number_of_global_variable_relationships_where_effect_") !== false){
                    $countField = "number_of_predictor_population_studies";
                }
                $exists = Writable::columnExists($referencedTable, $countField);
                if($exists){
                    QMLog::info("$referencedTable.$countField already exists");
                    continue;
                }
                if(isset($alreadyDone[$referencedTable][$countField])){
                    continue;
                }
                $comment = "Number of $tableTitle for this $foreignModel.
                    [Formula: update $referencedTable
                        left join (
                            select count($primaryKey) as total, $column
                            from $table
                            group by $column
                        )
                        as grouped on $referencedTable.$referencedColumn = grouped.$column
                    set $referencedTable.$countField = count(grouped.total)]";
                $alreadyDone[$referencedTable][$countField] =
                $str =
                    "\n\t\t\App\Storage\DB\Writable::statementIfNotExists(".'"'.
                    "\n\t\t\talter table $referencedTable".
                    "\n\t\t\t\tadd $countField int unsigned null".
                    "\n\t\t\t\t\tcomment '$comment';".
                    "\n\t\t".'"'.");\n";
                \App\Logging\ConsoleLog::info($str);
                $all .= $str;
            }
        }
        Migrations::makeMigration(QMStr::snakize(__FUNCTION__), $all);
        return $all;
    }
    public static function getUrlToCreateIndexMigration(string $sql): string {
        $qb = Writable::sqlToBuilder($sql);
        return $qb->getIndexMigrationUrl();
    }
	public static function getConnectionName(): string{return static::CONNECTION_NAME;}
protected static function getDBDriverName():string{return 'mysql';}
	public static function getDefaultDBName(): string{
		return Writable::getDbName();
	}
}
