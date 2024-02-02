<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDeprecationInspection */
/** @noinspection PhpDocMissingThrowsInspection */
namespace App\Properties;
use App\CodeGenerators\Swagger\SwaggerDefinitionProperty;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Storage\DB\QMDB;
use App\Traits\PropertyTraits\IsPrimaryKey;
use App\Types\QMStr;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InfyOm\Generator\Common\GeneratorFieldRelation;
use InfyOm\Generator\Utils\GeneratorForeignKey;
use InfyOm\Generator\Utils\GeneratorTable;
use App\CodeGenerators\ModelGenerator\Coders\Model\Rules\RulesGenerator;
class PropertiesGenerator {
	/** @var Table[] */
	public static $tableDetails = [];
	/** @var bool */
	public $defaultSearchable;
	/** @var BasePropertyGenerator[] */
	public $fields;
	/** @var array */
	public $ignoredFields;
	public $primaryKey;
	/** @var GeneratorFieldRelation[] */
	public $relations;
	public $rules;
	/** @var string */
	public $tableName;
	/** @var array */
	public $timestamps;
	/** @var Column[] */
	protected $columns;
	/** @var AbstractSchemaManager */
	protected $schemaManager;
	/**
	 * @param $tableName
	 * @param array $ignoredFields
	 * @param string $connectionName
	 */
	public function __construct($tableName, array $ignoredFields, string $connectionName){
		$this->tableName = $tableName;
		$this->ignoredFields = $ignoredFields;
		$this->configureSchemaManager($connectionName);
		$this->setColumns();
		$this->primaryKey = $this->getPrimaryKeyOfTable();
		$this->timestamps = static::getTimestampFieldNames();
		$this->defaultSearchable = config('infyom.laravel_generator.options.tables_searchable_default', false);
	}
	protected function setColumns(): void{
		$columns = $this->schemaManager->listTableColumns($this->tableName);
		$this->columns = [];
		foreach($columns as $column){
			if(!in_array($column->getName(), $this->ignoredFields)){
				$this->columns[] = $column;
			}
		}
		if(!$this->columns){le("no columns for $this->tableName");}
	}
	/**
	 * Get primary key of given table.
	 * @return string|null The column name of the (simple) primary key
	 */
	public function getPrimaryKeyOfTable(): ?string{
		$column = $this->listTableDetails($this->tableName)->getPrimaryKey();
		return $column ? $column->getColumns()[0] : '';
	}
	/**
	 * Cache tables because this can be slow if generating programmaticaly for many tables
	 * @param string $tableName
	 * @return Table
	 */
	public function listTableDetails(string $tableName): Table{
		$tables = self::$tableDetails;
		if(isset($tables[$tableName])){
			return $tables[$tableName];
		}
		$table = $this->schemaManager->listTableDetails($tableName);
		return self::$tableDetails[$tableName] = $table;
	}
	/**
	 * Get timestamp columns from config.
	 * @return array the set of [created_at column name, updated_at column name]
	 */
	public static function getTimestampFieldNames(): array{
		if(!config('infyom.laravel_generator.timestamps.enabled', true)){
			return [];
		}
		$createdAtName = config('infyom.laravel_generator.timestamps.created_at', 'created_at');
		$updatedAtName = config('infyom.laravel_generator.timestamps.updated_at', 'updated_at');
		$deletedAtName = config('infyom.laravel_generator.timestamps.deleted_at', 'deleted_at');
		return [$createdAtName, $updatedAtName, $deletedAtName];
	}
	public static function addPrimaryKeyTraits(){
		foreach(self::getNonBaseProperties() as $prop){
			if($prop->isPrimary){
				$prop->addTrait(IsPrimaryKey::class);
			}
		}
	}
	/**
	 * @return BaseProperty[]
	 */
	private static function getNonBaseProperties(): array{
		$classes = FileHelper::getClassesInFolder('app/Properties');
		$instances = [];
		foreach($classes as $class){
			if(strpos($class, "\\Base\\") !== false){
				continue;
			}
			if(strpos($class, \App\Properties\BaseProperty::class) !== false){
				continue;
			}
			try {
				$i = new $class;
			} catch (\Throwable $e) {
				QMLog::info(__METHOD__.": ".$e->getMessage());
				continue;
			}
			if(!$i instanceof BaseProperty){
				continue;
			}
			$instances[$class] = $i;
		}
		return $instances;
	}
	/**
	 * @return BaseProperty[]
	 */
	private static function getBaseProperties(): array{
		$props = FileHelper::getClassesInFolder('app/Properties/Base');
		$instances = [];
		foreach($props as $class){
			$instances[$class] = new $class;
		}
		return $instances;
	}
	/**
	 * @param string|null $column
	 * @param bool $overwrite
	 * @return array
	 */
	public function generatePropertyModelCodeFiles(string $column = null, bool $overwrite = false): array{
		$this->prepareFieldsFromTable();
		if(!$this->fields){le("no fields", $this);}
		$paths = [];
		foreach($this->fields as $field){
			if($column && $field->name !== $column){continue;}
			$paths[] = $field->generateBaseProperty($overwrite);
			$paths[] = $field->generateModelProperty($overwrite);
		}
		return $paths;
	}
	/**
	 * Prepares array of BaseProperty from table columns.
	 */
	public function prepareFieldsFromTable(): array
    {
		$rules = $this->getRules();
		$model = $this->getBaseModel();
		$hints = $model->getHints();
		foreach($this->columns as $column){
			$dbType = $column->getType()->getName();
			switch($dbType) {
				case 'integer':
					$field = $this->generateIntFieldInput($column, 'integer');
					$field->isSearchable = false;
					$field->isOrderable = true;
					break;
				case 'smallint':
					$field = $this->generateIntFieldInput($column, 'smallInteger');
					$field->isSearchable = false;
					$field->isOrderable = true;
					break;
				case 'bigint':
					$field = $this->generateIntFieldInput($column, 'bigInteger');
					$field->isSearchable = false;
					$field->isOrderable = true;
					break;
				case 'boolean':
					$name = Str::title(str_replace('_', ' ', $column->getName()));
					$field = $this->generateField($column, 'boolean', 'checkbox,1');
					$field->isSearchable = false;
					break;
				case BaseProperty::TYPE_DATETIME:
					$field = $this->generateField($column, BaseProperty::TYPE_DATETIME, 'date');
					$field->isSearchable = false;
					break;
				case 'datetimetz':
					$field = $this->generateField($column, 'dateTimeTz', 'date');
					$field->isSearchable = false;
					break;
				case 'date':
					$field = $this->generateField($column, 'date', 'date');
					$field->isSearchable = false;
					break;
				case 'time':
					$field = $this->generateField($column, 'time', 'text');
					$field->isSearchable = false;
					break;
				case 'decimal':
					$field = $this->generateNumberInput($column, 'decimal');
					$field->isSearchable = false;
					break;
				case 'float':
					$field = $this->generateNumberInput($column, 'float');
					$field->isSearchable = false;
					break;
				case 'text':
					$field = $this->generateField($column, 'text', 'textarea');
					$field->isSearchable = true;
					break;
				default:
					$field = $this->generateField($column, 'string', 'text');
					$field->isSearchable = true;
					break;
			}
			$field->dbType = $dbType;
			$field->type = SwaggerDefinitionProperty::dbTypeToSwaggerType($dbType);
			if(!$field->type){
				le('!$field->type');
			}
			$field->phpType = QMDB::dbTypeToPhpType($dbType);
			$field->isOrderable = false;
			if(strtolower($field->name) == 'password' || strtolower($field->name) == 'user_pass'){
				$field->htmlType = 'password';
			} elseif(strtolower($field->name) == 'email' || strtolower($field->name) == 'user_email'){
				$field->htmlType = 'email';
			} elseif(in_array($field->name, $this->timestamps)){
				$field->isSearchable = false;
				$field->isFillable = false;
				$field->inForm = false;
				$field->inIndex = false;
				$field->inView = false;
			}
			$field->canBeChangedToNull = !$column->getNotNull();
			$field->description = $column->getComment(); // get comments from table
			$hint = $hints[$field->name] ?? '';
			if(stripos($field->description, $hint) === false){
				$field->description .= " " . $hint;
			}
			$comment = $model_schema->comment ?? '';
			if(stripos($field->description, $comment) === false){
				$field->description .= " " . $comment;
			}
			$field->table = $this->tableName;
			if(isset($rules[$field->name])){
				$field->rules = $rules[$field->name];
			}
			$field->parentClass = QMStr::tableToFullClassName($this->tableName);
			$this->fields[$field->name] = $field;
		}
        return $this->fields;
	}
	/**
	 * @return array
	 */
	protected function getRules(): array{
		$m = $this->getBaseModel();
		if($m){
			return $m->getRules();
		}
		$c = $m->getConnection();
		$sm = $c->getDoctrineSchemaManager();
		$sm->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
		$generator = RulesGenerator::make($sm);
		$rules = $generator->getTableRules($this->tableName);
		return $this->rules = $rules;
	}
	/**
	 * Generates integer text field for database.
	 * @param string $dbType
	 * @param Column $column
	 * @return BasePropertyGenerator
	 */
	protected function generateIntFieldInput(Column $column, string $dbType): BasePropertyGenerator{
		$field = new BasePropertyGenerator();
		$field->name = $column->getName();
		$field->parseDBType($dbType);
		$field->htmlType = 'number';
		if($column->getAutoincrement()){
			$field->dbInput .= ',true';
		} else{
			$field->dbInput .= ',false';
		}
		if($column->getUnsigned()){
			$field->dbInput .= ',true';
		}
		return $this->checkForPrimary($field);
	}
	/**
	 * Check if key is primary key and sets field options.
	 * @param BasePropertyGenerator $field
	 * @return BasePropertyGenerator
	 */
	protected function checkForPrimary(BasePropertyGenerator $field): BasePropertyGenerator{
		if($field->name == $this->primaryKey){
			$field->isPrimary = true;
			$field->isFillable = false;
			$field->isSearchable = false;
			$field->inIndex = false;
			$field->inForm = false;
			$field->inView = false;
		}
		return $field;
	}
	/**
	 * Generates field.
	 * @param Column $column
	 * @param $dbType
	 * @param $htmlType
	 * @return BasePropertyGenerator
	 */
	protected function generateField(Column $column, $dbType, $htmlType): BasePropertyGenerator{
		$field = new BasePropertyGenerator();
		$field->name = $column->getName();
		$field->parseDBType($dbType, $column);
		$field->parseHtmlInput($htmlType);
		return $this->checkForPrimary($field);
	}
	/**
	 * Generates number field.
	 * @param Column $column
	 * @param string $dbType
	 * @return BasePropertyGenerator
	 */
	protected function generateNumberInput(Column $column, string $dbType): BasePropertyGenerator{
		$field = new BasePropertyGenerator();
		$field->name = $column->getName();
		$field->parseDBType($dbType . ',' . $column->getPrecision() . ',' . $column->getScale());
		$field->htmlType = 'number';
		return $this->checkForPrimary($field);
	}
	public function getBaseModel(): BaseModel {
		$class = $this->getClassName();
		return new $class();
	}
	/**
	 * @return BaseModel|string
	 */
	private function getClassName(){
		return QMStr::tableToFullClassName($this->tableName);
	}
	/**
	 * Prepares relations (GeneratorFieldRelation) array from table foreign keys.
	 */
	public function prepareRelations(){
		$foreignKeys = $this->prepareForeignKeys();
		$this->checkForRelations($foreignKeys);
	}
	/**
	 * Prepares foreign keys from table with required details.
	 * @return GeneratorTable[]
	 */
	public function prepareForeignKeys(): array{
		$tables = $this->schemaManager->listTables();
		$fields = [];
		foreach($tables as $table){
			$primaryKey = $table->getPrimaryKey();
			if($primaryKey){
				$primaryKey = $primaryKey->getColumns()[0];
			}
			$formattedForeignKeys = [];
			$tableForeignKeys = $table->getForeignKeys();
			foreach($tableForeignKeys as $tableForeignKey){
				$generatorForeignKey = new GeneratorForeignKey();
				$generatorForeignKey->name = $tableForeignKey->getName();
				$generatorForeignKey->localField = $tableForeignKey->getLocalColumns()[0];
				$generatorForeignKey->foreignField = $tableForeignKey->getForeignColumns()[0];
				$generatorForeignKey->foreignTable = $tableForeignKey->getForeignTableName();
				$generatorForeignKey->onUpdate = $tableForeignKey->onUpdate();
				$generatorForeignKey->onDelete = $tableForeignKey->onDelete();
				$formattedForeignKeys[] = $generatorForeignKey;
			}
			$generatorTable = new GeneratorTable();
			$generatorTable->primaryKey = $primaryKey;
			$generatorTable->foreignKeys = $formattedForeignKeys;
			$fields[$table->getName()] = $generatorTable;
		}
		return $fields;
	}
	/**
	 * Prepares relations array from table foreign keys.
	 * @param GeneratorTable[] $tables
	 */
	protected function checkForRelations(array $tables){
		// get Model table name and table details from tables list
		$modelTableName = $this->tableName;
		$modelTable = $tables[$modelTableName];
		unset($tables[$modelTableName]);
		$this->relations = [];
		// detects many to one rules for model table
		$manyToOneRelations = $this->detectManyToOne($tables, $modelTable);
		if(count($manyToOneRelations) > 0){
			$this->relations = array_merge($this->relations, $manyToOneRelations);
		}
		foreach($tables as $tableName => $table){
			$foreignKeys = $table->foreignKeys;
			$primary = $table->primaryKey;
			// if foreign key count is 2 then check if many to many relationship is there
			if(count($foreignKeys) == 2){
				$manyToManyRelation = $this->isManyToMany($tables, $tableName, $modelTable, $modelTableName);
				if($manyToManyRelation){
					$this->relations[] = $manyToManyRelation;
					continue;
				}
			}
			// iterate each foreign key and check for relationship
			foreach($foreignKeys as $foreignKey){
				// check if foreign key is on the model table for which we are using generator command
				if($foreignKey->foreignTable == $modelTableName){
					// detect if one to one relationship is there
					$isOneToOne = $this->isOneToOne($primary, $foreignKey, $modelTable->primaryKey);
					if($isOneToOne){
						$modelName = model_name_from_table_name($tableName);
						$this->relations[] = GeneratorFieldRelation::parseRelation('1t1,' . $modelName);
						continue;
					}
					// detect if one to many relationship is there
					$isOneToMany = $this->isOneToMany($primary, $foreignKey, $modelTable->primaryKey);
					if($isOneToMany){
						$modelName = model_name_from_table_name($tableName);
						$this->relations[] =
							GeneratorFieldRelation::parseRelation('1tm,' . $modelName . ',' . $foreignKey->localField);
					}
				}
			}
		}
	}
	/**
	 * Detect many to one relationship on model table
	 * If foreign key of model table is primary key of foreign table.
	 * @param GeneratorTable[] $tables
	 * @param GeneratorTable $modelTable
	 * @return array
	 */
	protected function detectManyToOne(array $tables, GeneratorTable $modelTable): array{
		$manyToOneRelations = [];
		$foreignKeys = $modelTable->foreignKeys;
		foreach($foreignKeys as $foreignKey){
			$foreignTable = $foreignKey->foreignTable;
			$foreignField = $foreignKey->foreignField;
			if(!isset($tables[$foreignTable])){
				continue;
			}
			if($foreignField == $tables[$foreignTable]->primaryKey){
				$modelName = model_name_from_table_name($foreignTable);
				$manyToOneRelations[] =
					GeneratorFieldRelation::parseRelation('mt1,' . $modelName . ',' . $foreignKey->localField);
			}
		}
		return $manyToOneRelations;
	}
	/**
	 * Detects many to many relationship
	 * If table has only two foreign keys
	 * Both foreign keys are primary key in foreign table
	 * Also one is from model table and one is from diff table.
	 * @param GeneratorTable[] $tables
	 * @param string $tableName
	 * @param GeneratorTable $modelTable
	 * @param string $modelTableName
	 * @return bool|GeneratorFieldRelation
	 */
	protected function isManyToMany(array $tables, string $tableName, GeneratorTable $modelTable,
		string $modelTableName){
		// get table details
		$table = $tables[$tableName];
		$isAnyKeyOnModelTable = false;
		// many to many model table name
		$manyToManyTable = '';
		$foreignKeys = $table->foreignKeys;
		$primary = $table->primaryKey;
		// check if any foreign key is there from model table
		foreach($foreignKeys as $foreignKey){
			if($foreignKey->foreignTable == $modelTableName){
				$isAnyKeyOnModelTable = true;
			}
		}
		// if foreign key is there
		if(!$isAnyKeyOnModelTable){
			return false;
		}
		foreach($foreignKeys as $foreignKey){
			$foreignField = $foreignKey->foreignField;
			$foreignTableName = $foreignKey->foreignTable;
			// if foreign table is model table
			if($foreignTableName == $modelTableName){
				$foreignTable = $modelTable;
			} else{
				$foreignTable = $tables[$foreignTableName];
				// get the many to many model table name
				$manyToManyTable = $foreignTableName;
			}
			// if foreign field is not primary key of foreign table
			// then it can not be many to many
			if($foreignField != $foreignTable->primaryKey){
				return false;
			}
			// if foreign field is primary key of this table
			// then it can not be many to many
			if($foreignField == $primary){
				return false;
			}
		}
		if(empty($manyToManyTable)){
			return false;
		}
		$modelName = model_name_from_table_name($manyToManyTable);
		return GeneratorFieldRelation::parseRelation('mtm,' . $modelName . ',' . $tableName);
	}
	/**
	 * Detects if one to one relationship is there
	 * If foreign key of table is primary key of foreign table
	 * Also foreign key field is primary key of this table.
	 * @param string $primaryKey
	 * @param GeneratorForeignKey $foreignKey
	 * @param string $modelTablePrimary
	 * @return bool
	 */
	protected function isOneToOne(string $primaryKey, GeneratorForeignKey $foreignKey, string $modelTablePrimary): bool{
		if($foreignKey->foreignField == $modelTablePrimary){
			if($foreignKey->localField == $primaryKey){
				return true;
			}
		}
		return false;
	}
	/**
	 * Detects if one to many relationship is there
	 * If foreign key of table is primary key of foreign table
	 * Also foreign key field is not primary key of this table.
	 * @param string $primaryKey
	 * @param GeneratorForeignKey $foreignKey
	 * @param string $modelTablePrimary
	 * @return bool
	 */
	protected function isOneToMany(string $primaryKey, GeneratorForeignKey $foreignKey,
		string $modelTablePrimary): bool{
		if($foreignKey->foreignField == $modelTablePrimary){
			if($foreignKey->localField != $primaryKey){
				return true;
			}
		}
		return false;
	}
	/**
	 * @param string $connectionName
	 * @throws DBALException
	 * @throws Exception
	 */
	protected function configureSchemaManager(string $connectionName): void{
		if(!empty($connectionName)){
			$this->schemaManager = DB::connection($connectionName)->getDoctrineSchemaManager();
		} else{
			$this->schemaManager = DB::getDoctrineSchemaManager();
		}
		$platform = $this->schemaManager->getDatabasePlatform();
		$defaultMappings = [
			'enum' => 'string',
			'json' => 'text',
			'bit' => 'boolean',
		];
		$mappings = config('infyom.laravel_generator.from_table.doctrine_mappings', []);
		$mappings = array_merge($mappings, $defaultMappings);
		foreach($mappings as $dbType => $doctrineType){
			/** @noinspection PhpUnhandledExceptionInspection */
			$platform->registerDoctrineTypeMapping($dbType, $doctrineType);
		}
	}
}
