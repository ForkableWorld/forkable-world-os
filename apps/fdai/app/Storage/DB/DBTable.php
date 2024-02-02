<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage\DB;
use App\Exceptions\InvalidDatabaseCredentialsException;
use App\Exceptions\ProtectedDatabaseException;
use App\Files\FileHelper;
use App\Files\Json\JsonFile;
use App\Files\ZipHelper;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Models\GlobalVariableRelationship;
use App\Models\BaseModel;
use App\Models\UserVariableRelationship;
use App\Models\User;
use App\Models\Variable;
use App\Properties\BaseProperty;
use App\Properties\User\UserIdProperty;
use App\Storage\S3\S3Private;
use App\Traits\LoggerTrait;
use App\Types\QMStr;
use App\Types\TimeHelper;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

class DBTable extends Table {
	use LoggerTrait;
	public const TABLE_ALIASES = [
		GlobalVariableRelationship::TABLE => 'global_variable_relationships',
		UserVariableRelationship::TABLE => 'user_variable_relationships',
		Variable::TABLE => 'global_variables',
	];
	private Connection|QMDB $db;
	/**
	 * @param $name
	 * @param QMDB $db
	 * @param array $columns
	 * @param array $indexes
	 * @param array $fkConstraints
	 * @param int $idGeneratorType
	 * @param array $options
	 * @throws DBALException
	 * @throws Exception
	 */
	public function __construct($name, Connection $db,
	                            array $columns = [],
	                            array $indexes = [],
	                            array $fkConstraints = [], $idGeneratorType = 0,
		                        array $options = []){
        $this->db = $db;
		$s = $this->getSchemaManager();
		// https://github.com/doctrine/dbal/issues/3161
		$s->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
		if($s->tablesExist([$name])){
			if(!$columns){$columns = $s->listTableColumns($name);}
			if(!$indexes){$indexes = $s->listTableIndexes($name);}
			if(!$fkConstraints){$fkConstraints = $s->listTableForeignKeys($name);}
		}
        parent::__construct($name, $columns, $indexes,
                            $fkConstraints, $idGeneratorType, $options);
    }
	/**
	 * @return DBTable[]
	 */
	public static function all(): array{
		return Writable::getDBTables();
	}
    public function getConnection(): Connection{
        return $this->db;
    }
	public static function toDisplayName(string $tableName): string {
		$tableName = DBTable::TABLE_ALIASES[$tableName] ?? $tableName;
		$tableName = str_replace('wp_', '', $tableName);
		return $tableName;
	}
	public function getConnectionName(): string{
        return $this->getConnection()->getName();
    }
	public static function find(string $table): DBTable{
		return Writable::db()->getDBTable($table);
	}
	/**
	 * @param string $column
	 * @return DBTable[]
	 */
	public static function withColumn(string $column): array{
		return Writable::getTablesWithColumn($column);
	}
	public function columnExists(string $name): bool {
		$columns = $this->getColumnNames();
        return in_array($name, $columns);
    }
    public function compressAndUpload(): string {
        $path = $this->getDumpPath();
        $zipPath = ZipHelper::zipLarge($this->getDumpPath(), $this->getZipPath());
        ConsoleLog::info("Compressing $path\n\tto $zipPath...");
        $dbName  = $this->getDBName();
        $date = TimeHelper::YYYYmmddd();
        $table = $this->getName();
        $s3Path = S3Private::S3_PATH_DB_BACKUPS."/$dbName/$date/$table.zip";
        ConsoleLog::info("Uploading $zipPath\n\tto $s3Path...");
        S3Private::uploadLargeFile($s3Path, $zipPath);
        return S3Private::uploadLargeFile($s3Path, $zipPath);
    }
	/**
	 * @return QMDB|Connection
	 */
	public function db(): QMDB|Connection{
		return $this->db;
	}
	public function getDumpPath(string $folder = null):string{
        $table = $this->getName();
        if(!$folder){
            $folder = $this->db->getDumpPath();
        }
        $path = $folder."/$table.json";
        return FileHelper::absPath($path);
    }
    public function getZipPath():string{
        $table = $this->getName();
        $path = $this->db->getDumpPath()."/$table.zip";
        return $path;
    }
    public function getDBName():string{
        return $this->db->getDbName();
    }
    public function dumpXML(): void {
        $name = $this->getName();
        $dbName = $this->getDbName();
        $path = FileHelper::absPath(static::getDumpPath()."/$name.xml");
        $credentials = $this->db->credentialsCommand();
        try {
	        $this->db->exec("mysqldump --max-allowed-packet=16M --no-create-info --xml $credentials $dbName $name > $path");
        } catch (InvalidDatabaseCredentialsException $e) {le($e);}
    }
    public function dumpStructure(string $folder = null): string {
        return $this->dumpToJson($folder);
    }
    public function dumpToJson(string $folder = null): string {
        $dbName = $this->getDbName();
        $table = $this->getName();
        $folder = FileHelper::absPath($folder);
        $path = $this->getDumpPath($folder);
        FileHelper::createDirectoryIfNecessary($folder);
        FileHelper::deleteFile($path, __METHOD__);
        ConsoleLog::info("Dumping $dbName.$table\n\tto $path...");
        $data = $this->get();
        $path = JsonFile::write($path, $data);
		if(!FileHelper::fileExists($path)){le('!FileHelper::fileExists($path)');}
        return $path;
    }
    /**
     * @param QMDB|string $destDBClass
     */
    public function copy(QMDB|string $destDBClass){
        $dbName = $this->getDbName();
        $table = $this->getName();
        $destDB = $destDBClass::CONNECTION_NAME;
        ConsoleLog::info("Copying $table from $dbName to $destDB...");
        $path = $this->dumpToJson();
        $destDBClass::importTableFromJson($path);
    }
    /**
     * @param QMDB|string $destDBClass
     * @return string
     */
    public function copyAndUpload(QMDB|string $destDBClass): string{
        $path = $this->dumpToJson();
        $destDBClass::importTableFromJson($path);
        return $this->compressAndUpload();
    }
	/**
	 * @param string $column
	 * @return DBColumn
	 * @throws SchemaException
	 */
	public function getDBColumn(string $column): DBColumn{
        if(!$this->_columns){
            $this->getColumns();
        }
		$column = $this->getColumn($column);
		return new DBColumn($column, null, null, $this);
	}
	public function getSizeInMB(): int {
        $sizes = $this->db->getTableSizesInMbDescending();
        return $sizes[$this->getName()];
    }
	/**
	 * @return string
	 */
	public function __toString(){return $this->_name;}
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasColumn($name): bool{
		if(!$this->_columns){$this->getColumns();} // Not populated for some reason
		return parent::hasColumn($name);
	}
	/**
	 * @return DBColumn[]
	 */
	public function getColumns(): array{
		$cols = $this->_columns;
		if(!$cols){
			$s = $this->getSchemaManager();
			$cols = $s->listTableColumns($this->getName());
		}
		foreach($cols as $key =>$val){
			$cols[$key] = new DBColumn($val, null, null, $this);
		}
		return $this->_columns = $cols;
	}
	/**
	 * @return string
	 * @noinspection SqlResolve
	 */
	public function getCreateTableSQL(): string{
		$additionalFields = [];
		foreach($this->getColumns() as $column){
			$additionalFields[] .= $column->getSqlDeclaration();
		}
		$additionalFields = implode(",\n\t\t\t", $additionalFields);
		$singular = QMStr::singularize(str_replace("_", " ", $this->getName()));
		return "
		create table {$this->getName()} (
		        id                     int(11) unsigned auto_increment  primary key COMMENT 'Automatically generated unique id for the $singular',
                client_id              varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci null COMMENT 'The ID for the API client that created the record',
                created_at             timestamp        default CURRENT_TIMESTAMP not null COMMENT 'The time the record was originally created',
                deleted_at             timestamp                                  null COMMENT 'The time the record was deleted',
                updated_at             timestamp        default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP COMMENT 'The time the record was last modified',
                user_id                bigint unsigned                            not null COMMENT 'The user ID for the owner of the record',
                $additionalFields
            );
            alter table {$this->getName()} add constraint {$this->getName()}_client_id_fk
                foreign key (client_id) references oa_clients (client_id);
            alter table {$this->getName()} add constraint {$this->getName()}_wp_users_ID_fk
		        foreign key (user_id) references wp_users (ID);
		";
	}
	/**
	 * @return AbstractSchemaManager
	 */
	protected function getSchemaManager(): AbstractSchemaManager{
		$s = $this->db->getConnection()->getDoctrineSchemaManager();
		return $s;
	}
	/**
	 * @return int[]|string[]
	 */
	protected function getColumnNames(): array{
		return array_keys($this->_columns);
	}

	public function isOctoberOrWP(): bool{
		return str_starts_with($this->getName(), 'o_') || str_starts_with($this->getName(), 'wp_');
	}
	/**
	 * @param string|QMDB $destDB
	 * @return void
	 */
	public function copyTableSchema(QMDB|string $destDB): void{
		$destName = $destDB::getDbName();
		$tableName = $this->getName();
		$sourceDB = $this->getDBName();
		$destDB::disableForeignKeyConstraints();
		$sql = "CREATE TABLE $destName.$tableName LIKE $sourceDB.$tableName;";
		$destDB::statementIfNotExists($sql);
	}
	/**
	 * @return QMQB|Builder
	 */
	public function qb(): QMQB|Builder{
		return $this->db()->table($this->getName());
	}
    public function deleteNonPublicUserData(){
		if($this->hasColumn('user_id')){
			$this->qb()->whereNotIn("user_id", UserIdProperty::getPublicUserIds())->delete();
		} else {
			QMLog::info("No user_id column found in {$this->getName()}");
		}
    }
	public function rename(string $new_name){
		$current_name = $this->getName();
		$this->db()->statement("rename table $current_name to $new_name;", []);
	}
	public function snakize_column_names(){
		$columns = $this->getColumns();
		foreach($columns as $column){
			$column->snakize();
		}
	}
	/** @noinspection SqlResolve */
	public function addPrimaryKey(){
		if($this->columnExists('id')){
			$this->db()->statement("alter table {$this->getName()}
                 add constraint {$this->getName()}_pk
                     primary key (id)");
			$this->db()->statement("alter table {$this->getName()}
                 modify id int auto_increment");
		} else {
			$this->db()->statement("alter table {$this->getName()} add column `id` int(10) unsigned primary KEY AUTO_INCREMENT;", []);
		}
	}
	/**
	 * @return DBColumn[]
	 */
	public function getTimestampColumns(): array{
		$columns = $this->getColumns();
		$timestamps = [];
	    foreach($columns as $column){
		    $type = $column->getType();
		    if($column->isTimestamp()){
			    $timestamps[] = $column;
		    } elseif($column->getName() === QMDB::FIELD_UPDATED_AT){
			    $m = "$column should be a timestamp but type is $type";
			    //throw new LogicException($m);
			    QMLog::info($m);
		    }
	    }
		if(!$timestamps){
			$m = "No timestamp columns found in {$this->getName()}";
			ConsoleLog::info($m);
		}
	    return $timestamps;

    }
	public function count(): int{
		return $this->qb()->count();
	}
	/**
	 * @return void
	 * @throws ProtectedDatabaseException
	 */
	public function truncate(): void{
		ConsoleLog::warning("Truncating {$this->getName()}");
		$this->qb()->truncate();
	}

    /**
     * @throws SchemaException
     */
    public function setColumns(array $getDBColumns)
    {
        foreach ($getDBColumns as $column) {
            $this->_addColumn($column);
        }
    }
    public function get(): Collection {
        return $this->qb()->get();
    }

    public function disableForeignKeyConstraints()
    {
		$foreignKeys = $this->getForeignKeys();
		foreach($foreignKeys as $foreignKey){
			//$foreignKey->disable();
		}
	}
	public function getUserIdColumn(): ?string {
		if($this->hasColumn('user_id')){
			return 'user_id';
		}
		if($this->hasColumn('post_author')){
			return 'post_author';
		}
		if($this->getName() === User::TABLE){
			return User::FIELD_ID;
		}
		return null;
	}
	public function hasUserIdColumn(): bool{
		return $this->getUserIdColumn() !== null;
	}
	public function where(): QMQB|Builder{
		$qb = $this->qb();
		return $qb->where(...func_get_args());
	}
	public function addCommentsToDocsTable(BaseModel $model): array {
		$builder = $this->db->table('docs_models');
		$tableComment = $this->getComment();
		if(!$tableComment){
			$tableComment = "No comment";
			$tableComment = $model::getClassDescription();
		}
		$name = $this->getName();
		$tableAliases = self::TABLE_ALIASES;
		$name = $tableAliases[$name] ?? $name;
		$name = str_replace('wp_', '', $name);
		$builder->insert($json[] = [
			'model' => $name,
			'description' => $tableComment
		]);
		$columns = $this->getColumns();
		foreach($columns as $column){
			$comment = $column->getComment();
			if(!$tableComment){
				$comment = "No comment";
				$comment = $model->getPropertyModel($column);
			}
			$comment = str_replace(' correlation ', ' predictive relationship ', $comment);
			$builder->insert($json[] = [
				'model' => $name,
				'attribute' => $column->getName(),
				'description' => $comment
			]);
		}
		return $json;
	}
	/**
	 * @return \App\Models\BaseModel
	 */
	public function getModel(): BaseModel {
		$class = QMStr::tableToFullClassName($this->getName());
		$m = new $class;
		return $m;
	}
	/**
	 * @return BaseProperty[]
	 */
	public function getPropertyModels(): array{
		$model  = $this->getModel();
		return $model->getPropertyModels();
	}
}
