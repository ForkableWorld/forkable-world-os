<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
namespace App\Files\Json;
use App\CodeGenerators\CodeGenerator;
use App\Files\FileHelper;
use App\Folders\DynamicFolder;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Storage\DB\DBColumn;
use App\Storage\DB\DBTable;
use App\Storage\DB\Migrations;
use App\Storage\DB\TestDB;
use App\Storage\DB\Writable;
use App\Types\QMStr;
use App\Utils\EnvOverride;
use Illuminate\Support\Facades\DB;
use stdClass;
class JsonToPhpFile extends JsonFile {
	protected string $connection = TestDB::CONNECTION_NAME;
	protected string $schema = TestDB::DB_NAME;
	protected ?string $tableName = null;
	protected array $objects;
    private ?DBTable $dbTable = null;

    /**
	 * JsonResponseFile constructor.
	 */
	public function __construct(string $tableName, $rawData, string $connection){
		$this->setObjects($rawData);
		$this->tableName = $tableName;
		$this->connection = $connection;
		parent::__construct(DynamicFolder::FOLDER_RESOURCES_JSON_RESPONSES . "/" . $this->tableName . ".json");
	}
	/**
	 * @param string $filePath
	 * @param $data
	 * @return string Absolute file path
	 */
	public static function writeJsonFileByPath(string $filePath, $data): string{
		$folder = FileHelper::getFolderFromPath($filePath);
		$file = FileHelper::getFileNameFromPath($filePath);
		return FileHelper::writeJsonFile($folder, $data, $file);
	}
	/**
	 * @return void
	 * @throws \Exception
	 */
	private function readObjects(): array {
		// Checks if JSON file exists, if not create
		if(!file_exists($this->getRealPath())){
			$this->save();
		}
		// Read content of JSON file
		$str = file_get_contents($this->getRealPath());
		$arr = json_decode($str);
		// Check if its arrays of jSON
		if(is_object($arr)){
			ConsoleLog::info("Wrapping object in array");
			$arr = [$arr];
		} // An invalid jSON file
		elseif(!is_array($arr)){
			throw new \Exception('json is invalid: ' . $str);
		}
		$this->setObjects($arr);
		return $arr;
	}
	/**
	 * @return object[]
	 */
	public function getObjects(): array{
		return $this->objects;
	}
	/**
	 * @param string $connection
	 * @return JsonToPhpFile
	 */
	public function setConnection(string $connection): JsonToPhpFile{
		$this->connection = $connection;
		return $this;
	}
	/**
	 * @return string
	 */
	protected function getTableName(): string{
        if($this->tableName){
            return $this->tableName;
        }
		$table = pathinfo($this->getSQLPath(), PATHINFO_FILENAME);
		return $this->tableName = $table;
	}
	/**
	 * @return string
	 */
	protected function getInsertStatements(): string{
		$sql = "";
		$table = $this->getDBTable();
		$objects = $this->getObjects();
		foreach($objects as $object){
			$arr = ( array )$object;
			foreach($arr as $key => $value){
				if(!$table->columnExists($key)){
					unset($arr[$key]);
					QMLog::error("Skipping non-existent column: $key");
				}
			}
			$values = array_map(function($vv){
				$vv = (is_array($vv) || is_object($vv) ? serialize($vv) : $vv);
				return sprintf("'%s'", addslashes((string)$vv));
			}, array_values($arr));
			$cols = array_map(function($col){
				return sprintf("`%s`", $col);
			}, array_keys($arr));
			/** @noinspection SqlResolve */
			$sql = sprintf("INSERT INTO `%s` ( %s ) VALUES ( %s );\n", $this->getTableName(), implode(', ', $cols),
				implode(', ', $values));
		}
		return $sql;
	}
	private function getDBTable(): DBTable{
        if($this->dbTable){
            return $this->dbTable;
        }
        $this->dbTable = new DBTable($this->getTableName(), Writable::db());
        $this->dbTable->setColumns($this->getDBColumns());
		return $this->dbTable;
	}
	/**
	 * @return \Illuminate\Database\Connection|\Illuminate\Database\ConnectionInterface
	 */
	private function db(){
		return DB::connection($this->connection);
	}
	/**
	 * @return DBColumn[]
	 */
	protected function getDBColumns(): array{
		$columns = [];
		$arr = $this->readObjects();
		$firstObject = $arr[0];
		foreach($firstObject as $property => $value){
			$property = $this->toFieldName($property);
			if($value === null){
				QMLog::error("Skipping column for property with null value: $property");
				continue;
			}
			$c = DBColumn::fromData($property, $value);
			if(in_array($property, Migrations::GLOBAL_COLUMNS)){
				$property = "external_" . $property;
				$c = new DBColumn($c, $property, null, $this->getDBTable());
			}
			$columns[$property] = $c;
		}
		//		$columns[BaseCreatedAtProperty::NAME] = (new BaseCreatedAtProperty())->getDBColumn();
		//		$columns[BaseUpdatedAtProperty::NAME] = (new BaseUpdatedAtProperty())->getDBColumn();
		//		$columns[BaseDeletedAtProperty::NAME] = (new BaseDeletedAtProperty())->getDBColumn();
		//		$columns[BaseIdProperty::NAME] = (new BaseIdProperty())->getDBColumn();
		//		$columns[BaseClientIdProperty::NAME] = (new BaseClientIdProperty())->getDBColumn();
		//		$columns[BaseUserIdProperty::NAME] = (new BaseUserIdProperty())->getDBColumn();
		return $columns;
	}
	/**
	 * this will create the input table including
	 * the appropriate primary column as specified
	 * in the constructor
	 */
	public function getTableCreationSQL(): string{
		$table = $this->getDBTable();
		$sql = $table->getCreateTableSQL();
		return $sql;
	}
	/**
	 * @param string $table
	 * @param $data
	 * @param string|null $connection
	 * @return array
	 */
	public static function generateModel(string $table, $data, string $connection = null): array{
		if(!EnvOverride::isLocal()){
			QMLog::info("Skipping " . __METHOD__ . " because we're not local");
			return [];
		}
		$me = new static($table, $data, $connection);
		return $me->generateLaravelModel();
	}
	public function getContents(): string{
		$c = $this->originalContent;
		if(!$c){
			$c = $this->originalContent = FileHelper::getContents($this->getPath());
		}
		return $c;
	}
	public function save(): string {
		return $this->writeContents(json_encode($this->getObjects()));
	}
	/**
	 * @return array Files created
	 */
	public function generateLaravelModel(): array{
		if(!$this->tableExists()){
			$this->createTable();
		} else{
			$this->logError($this->getTableName() . " already exists so generating from existing table");
		}
		$this->db()->statement($this->getInsertStatements());
		return CodeGenerator::tableToBaseModel($this->getTableName(), $this->connection);
	}
	private function tableExists(): bool{
		return $this->db()->getDoctrineSchemaManager()->tablesExist([$this->getTableName()]);
	}
	/**
	 * @return void
	 */
	public function createTable(): void{
		$this->createMigration();
		TestDB::migrate();
	}
	/**
	 * @return string
	 */
	public function getSQLPath(): string{
		return str_replace(".json", ".sql", $this->getPath());
	}
	/**
	 * @return string
	 */
	public function createMigration(): string{
		$this->save();
		$sql = $this->getTableCreationSQL();
		return Migrations::makeMigration($this->getFilenameWithoutExtension(), $sql, $this->connection);
	}
	/**
	 * @param array|string|object $rawData
	 */
	public function setObjects($rawData): void{
		$exampleObjects = QMStr::jsonDecodeIfNecessary($rawData);
		if(is_object($exampleObjects) ||
            (is_array($exampleObjects) && !isset($exampleObjects[0]))){
			$exampleObjects = [$exampleObjects];
		}
		$snakes = [];
		foreach($exampleObjects as $object){
			$snake = new stdClass();
			foreach($object as $key => $value){
				$field = $this->toFieldName($key);
				$snake->$field = $value;
				$snakes[] = $snake;
			}
		}
		$this->objects = $snakes;
	}
	/**
	 * @param $property
	 * @return array|null|string|string[]
	 */
	protected function toFieldName($property){
		$property = QMStr::snakize($property);
		$property = preg_replace('/[^a-zA-Z0-9_]/', '_', $property);
		return $property;
	}
}
