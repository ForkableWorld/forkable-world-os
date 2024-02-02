<?php

namespace App\Storage;

use App\Computers\ThisComputer;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Models\ConnectorRequest;
use App\Models\Credential;
use App\Models\Measurement;
use App\Models\OAClient;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserTag;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Models\VariableCategory;
use App\Models\WpPost;
use App\Properties\Base\BaseCreatedAtProperty;
use App\Properties\Base\BaseIsGoalProperty;
use App\Properties\Base\BaseSlugProperty;
use App\Properties\User\UserSubscriptionProviderProperty;
use App\Properties\UserTag\UserTagTaggedUserVariableIdProperty;
use App\Properties\UserTag\UserTagTagUserVariableIdProperty;
use App\Properties\VariableCategory\VariableCategoryControllableProperty;
use App\Storage\DB\AbstractPostgresDB;
use App\Storage\DB\DBTable;
use App\Storage\DB\QMDB;
use App\Storage\DB\QMQB;
use App\Types\QMStr;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Log;
use PDOException;
class DatabaseSynchronizer
{
    public const DEFAULT_LIMIT = 1000;

    public bool $cli = false;

    public bool $upsert = false;

    public int $limit = self::DEFAULT_LIMIT;

    public array $tables;

    public array $skipTables = [ConnectorRequest::TABLE, Credential::TABLE];

    public bool $migrate = true;

    public string $sourceConnectionName;

    public string $destinationConnectionName;

    public bool $truncate = false; // calls down() on all migrations

    private QMDB $sourceDB;

    private QMDB $destinationDB;
    private int $rowCount;
    private string $currentTable;
	private bool $syncStructure = false;
	public function __construct(QMDB $sourceDB, QMDB $destinationDB)
    {
        $this->setSourceDB($sourceDB);
        $this->setDestinationDB($destinationDB);
    }
	/**
	 * @return string
	 */
	public function getDestinationIdentifier(): string {
		$destination = $this->destinationDB;
		$destId = $destination::getHost() . "@" . $destination::getDbName();
		return $destId;
	}
	/**
	 * @return string
	 */
	public function getSourceIdentifier(): string {
		$sourceDB = $this->sourceDB;
		$destId = $sourceDB::getHost() . "@" . $sourceDB::getDbName();
		return $destId;
	}
	/**
	 * @param \Illuminate\Database\Connection|\Illuminate\Database\ConnectionInterface $connection
	 * @return DatabaseSynchronizer
	 */
	public function setDestinationDB(QMDB $connection): DatabaseSynchronizer{
		$this->destinationConnectionName = $connection->getName();
		$this->destinationDB = $connection;
		return $this;
	}
	/**
	 * @param \Illuminate\Database\Connection|\Illuminate\Database\ConnectionInterface $connection
	 * @return DatabaseSynchronizer
	 */
	public function setSourceDB(QMDB $connection ): DatabaseSynchronizer{
		$this->sourceConnectionName = $connection->getName();
		$this->sourceDB = $connection;
		return $this;
	}
	public function sync(): void
    {

		$destination = $this->destinationDB;
	    $sourceId = $this->getSourceIdentifier();
	    $destId = $this->getDestinationIdentifier();
	    QMLog::info("Syncing database from $this->sourceConnectionName ($sourceId) to $this->destinationConnectionName ($destId)");
	    if (!$destination::tableExists(User::TABLE)) {$this->migrate();}
        foreach ($this->getTables() as $currentTable) {
			if(!$destination::tableExists($currentTable)){
				ConsoleLog::error("Table $currentTable does not exist in destination DB $destId");
				continue;
			}
			if(!$destination::isSQLite()){
				$destination::disableForeignKeyConstraints(null, $currentTable);
			}
            $this->setCurrentTable($currentTable);
            QMLog::info(PHP_EOL . PHP_EOL . "Syncing Table: $currentTable from $sourceId => $destId");
            if (!Schema::connection($this->sourceConnectionName)->hasTable($currentTable)) {
                QMLog::info("Table '$currentTable' does not exist in $sourceId", 'error');
                continue;
            }
			if($this->syncStructure){
				$this->syncTableStructure();
			}
            if($this->upsert) {
                $this->upsertRows();
            } else {
				try {
					$this->insertMissingRows();
				} catch (\Throwable $e) {
				    ConsoleLog::exception($e);
				}
            }
	        $destination::enableForeignKeyConstraints();
        }
        QMLog::info("Synchronization from $sourceId to $destId done!", 'info');
    }

    private function createTable(array $columns): void
    {
        QMLog::info("Creating '$this->destinationConnectionName.$this->currentTable' table", 'warn');

        Schema::connection($this->destinationConnectionName)->create($this->currentTable, function (Blueprint $table_bp) use ($columns) {
            foreach ($columns as $column) {
                $type = Schema::connection($this->sourceConnectionName)->getColumnType($this->currentTable, $column);

                $table_bp->{$type}($column)->nullable();

                QMLog::info("Added $type('$column')->nullable()");
            }
        });
    }

    private function addColumn(string $column): void
    {
        QMLog::info("Updating $column in table $this->currentTable", 'warn');
        Schema::connection($this->destinationConnectionName)->table($this->currentTable, function (Blueprint $table_bp) use ($column) {
            $type = Schema::connection($this->sourceConnectionName)->getColumnType($this->currentTable, $column);

            $table_bp->{$type}($column)->nullable();

            QMLog::info("Added {$type}('$column')->nullable()");
        });
    }

    public function setSkipTables(array $skipTables): static
    {
        $this->skipTables = $skipTables;

        return $this;
    }

    public function setTables(array $tables): static
    {
        $this->tables = $tables;

        return $this;
    }

    public function setLimit(int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    public function setOptions(array $options): static
    {
        foreach ($options as $option => $value) {
            if (! isset($this->{$option})) {
                $this->{$option} = $value;
            }
        }

        return $this;
    }

    protected function getSourceDb(): QMDB
    {
        return $this->sourceDB;
    }

    protected function getDestinationDB(): QMDB
    {
        return $this->destinationDB;
    }

    public function getTables(): array
    {
        if (empty($this->tables)) {
	        $sourceDb = $this->getSourceDb();
	        $this->tables = $sourceDb->getTableNames();
	        array_unshift($this->tables,
						  VariableCategory::TABLE,
						  Unit::TABLE,
                          Variable::TABLE,
						  UserVariable::TABLE,
						  UserTag::TABLE,
	                      Measurement::TABLE,
						  WpPost::TABLE,
	                      User::TABLE,
	                      OAClient::TABLE);
			$this->tables = array_unique($this->tables);
        }

        return array_filter($this->tables, function ($table) {
            return ! in_array($table, $this->skipTables, true);
        });
    }

    /**
     * Check if tables and columns are present
     * Create or update them if not.
     *
     */
    public function syncTableStructure(): void
    {
        $destinationSchema = Schema::connection($this->destinationConnectionName);
        $sourceSchema = Schema::connection($this->sourceConnectionName);
        $sourceColumns = $sourceSchema->getColumnListing($this->currentTable);

        if ($destinationSchema->hasTable($this->currentTable)) {
            $destinationColumns = $destinationSchema->getColumnListing($this->currentTable);
            foreach ($sourceColumns as $column) {
                if (in_array($column, $destinationColumns)) {
                    QMLog::info("Has column '$column'");
                    continue;
                }

                $this->addColumn($column);
            }

            return;
        }

        $this->createTable($sourceColumns);
    }

    /**
     * Fetch all rows in $this->from and insert or update $this->to.
     *
     */
    public function upsertRows(): void
    {
		$sourceId = $this->getSourceIdentifier();
		$destId = $this->getDestinationIdentifier();
        QMLog::info("Syncing rows for '$this->currentTable' in $sourceId to $destId");
        $queryColumn = Schema::connection($this->sourceConnectionName)->getColumnListing($this->currentTable)[0];
        $statement = $this->prepareForUpserts();
        $destinationRowCount = $this->getDestinationQB()->count();
        if($this->rowCount === $destinationRowCount) {
            QMLog::info("No new rows to sync for '$this->currentTable' as both have $destinationRowCount");
            return;
        }
        $soFar = 0;
        while ($row = $statement->fetch(\PDO::FETCH_OBJ)) {
            $soFar++;
            $exists = $this->getDestinationQB()->where($queryColumn, $row->{$queryColumn})->first();

            if (! $exists) {
                $this->getDestinationQB()->insert((array) $row);
            } else {
                $this->getDestinationQB()->where($queryColumn, $row->{$queryColumn})->update((array) $row);
            }

	        if($soFar % 100 === 0) {
		        $percent = round($soFar / $this->rowCount * 100, 0);
		        QMLog::info("Synced $percent% ($soFar rows of $this->rowCount) for '$this->currentTable' from $sourceId to $destId");
	        }
        }

	    QMLog::info("Done syncing $this->currentTable from $sourceId to $destId");
    }

    /**
     * @return \PDOStatement
     */
    private function prepareForUpserts(): \PDOStatement
    {
        QMLog::info("Preparing to insert '$this->currentTable' into ".$this->getDestinationIdentifier());
        $pdo = $this->getSourceDb()->getPdo();
        $builder = $this->getSourceQB();
        $statement = $pdo->prepare($builder->toSql());

        if (! $statement instanceof \PDOStatement) {
            throw new PDOException("Could not prepare PDOStatement for $this->currentTable");
        }

        $bindings = $builder->getBindings();
        QMLog::info($statement->queryString, print_r($bindings, true));
        $statement->execute($bindings);
        $this->rowCount = $statement->rowCount();


        if ($this->rowCount > 0) {
            QMLog::info("Synchronizing $this->rowCount rows for $this->currentTable", 'comment');
            if ($this->cli) {
                $this->cli->progressBar = $this->cli->getOutput()->createProgressBar($this->rowCount);
            }
        } else {
            QMLog::info('No rows...', 'comment');
        }


        if ($this->truncate) {
            $this->getDestinationDB()->table($this->currentTable)->truncate();
        }

        return $statement;
    }

    /**
     * @return string
     */
    public function getCurrentTable(): string
    {
        return $this->currentTable;
    }

    /**
     * @param string $currentTable
     * @return DatabaseSynchronizer
     */
    public function setCurrentTable(string $currentTable): DatabaseSynchronizer
    {
        $this->currentTable = $currentTable;
        return $this;
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getSourceQB(): \Illuminate\Database\Query\Builder
    {
        return $this->sourceDB->table($this->currentTable);
    }

    /**
     * @return \Illuminate\Database\Query\Builder|QMQB
     */
    private function getDestinationQB(): \Illuminate\Database\Query\Builder|QMQB
    {
        return $this->getDestinationDB()->table($this->getCurrentTable());
    }

    private function insertMissingRows(): void
    {
	    $this->updatePrimaryKeySequence();
	    $missingIds = $this->getMissingIds();
	    $offset = 0;
        $total = $missingIds->count();
		$start = microtime(true);
        while ($ids = $missingIds->slice($offset, $this->limit)) {
            if(!$ids->count()) {
                break;
            }
			try {
				$this->insertBatch($ids);
			} catch (\Throwable $e) {
				if(str_contains($e->getMessage(), "number of parameters") || str_contains($e->getMessage(), "too many")) {
					$this->limit = $this->limit/2;
					QMLog::info("Retrying $this->currentTable with smaller batch size: $this->limit\n\tbecause ".
					                QMStr::truncate($e->getMessage(), 100));
					continue;
				} else {
					le($e);
				}
			}
	        if(!isset($duration)){$duration = microtime(true) - $start;}
	        $remainingRows = $total - $offset;
	        $remainingBatchCount = $remainingRows/ $this->limit;
	        $remainingTime = $remainingBatchCount * $duration;
	        $remainingMinutes = round($remainingTime / 60, 0);
			$percent = round($offset / $total * 100, 0);
	        $offset += $this->limit;
	        QMLog::info("Inserted $percent% ($offset of $total) rows for $this->currentTable.
$remainingMinutes minutes remaining...");
        }
	    $this->updatePrimaryKeySequence();
    }
    public function syncPublic(): void
    {
        $this->disableForeignKeyConstraints();
	    $this->truncate = false; // Calls down() on all migrations
	    $this->migrate = true;
	    $this->tables = QMDB::GLOBAL_TABLES;
	    $this->sync();
	    $this->enableForeignKeyConstraints();
    }
    public static function syncStatic(QMDB $sourceDB, QMDB $destinationDB): void{
		//CacheManager::clear();
	    ThisComputer::removeMemoryLimit();
		(new static($sourceDB, $destinationDB))->sync();
    }
	private function getPrimaryKey(): string{
		if(!$this->modelExists()){
			throw new ModelNotFoundException("Model for $this->currentTable does not exist");
		}
		$model = $this->getModel();
		return $model->getPrimaryKey();
	}
	private function getModel(): BaseModel {
		$class = $this->getClass();
		return new $class();
	}
	private function getClass(): \App\Models\BaseModel|string{
		$table = $this->getCurrentTable();
		return QMStr::tableToFullClassName($table);
	}
	private function modelExists(): bool{
		$class = $this->getClass();
		return class_exists($class);
	}
	/**
	 * @param Collection $ids
	 */
	private function insertBatch(\Illuminate\Support\Collection $ids): void {
		$destinationQB = $this->getDestinationQB();
		$key = $this->getPrimaryKey();
		$sourceQB = $this->getSourceQB();
		$builder = $sourceQB->whereIn($key, $ids->values());
		$rows = $builder->get();
		//$rows = json_decode(json_encode($rows), true);
		$objForSomeReason = $rows->all();
		$arr = [];
		foreach($objForSomeReason as $i => $row){
			$row = (array)$row;
			$arr[] = $this->setDefaultsIfEmpty($row);
		}
		try {
			$destinationQB->insert($arr);
		} catch (\Throwable $e){
			if(str_contains($e->getMessage(), "slug")) {
				foreach($arr as $i => $row){
					try {
						$destinationQB->insert([$row]);
					} catch (\Throwable $e){
					    QMLog::info(__METHOD__.": ".$e->getMessage());
						if(empty($row['slug'])){
							$row['slug'] = QMStr::slugify($row["user_login"] ?? $row['name']);
						} else {
							$row['slug'] = $row['slug'] . "-" . ($row['id'] ?? $row['ID']);
						}
						$destinationQB->insert([$row]);
					}
				}
			} else {
				le($e);
			}
		}
	}
	/**
	 * @return Collection
	 */
	private function getMissingIds(): Collection{
		$key = $this->getPrimaryKey();
		$destinationQB = $this->getDestinationQB();
		$sourceIds = $this->getSourceQB()->select($key)->pluck($key);
		$destinationIds = $destinationQB->pluck($key);
		$missingIds = $sourceIds->diff($destinationIds->values());
		return $missingIds;
	}
	private function disableForeignKeyConstraints(): void {
		try {
			$this->destinationDB->disableForeignKeyConstraints();
		} catch (\Illuminate\Database\QueryException  $e) {
			Log::info(__METHOD__.": ".$e->getMessage());
		}
	}
	/**
	 * @return void
	 */
	private function enableForeignKeyConstraints(): void{
		try {
			$this->destinationDB->enableForeignKeyConstraints();
		} catch (\Illuminate\Database\QueryException  $e) {
			Log::info(__METHOD__.": ".$e->getMessage());
		}
	}
	/**
	 * @return void
	 */
	private function migrate(): void{
		QMLog::info('migrating...');
		$dest = $this->getDestinationDB();
		$dest->migrate();
	}
	/**
	 * @param string $arrKey
	 * @param mixed $value
	 * @param string $default
	 * @return mixed
	 */
	private function setDefaultIfEmpty(string $arrKey, array $value, string $default): array {
		if(array_key_exists($arrKey, $value) && empty($value[$arrKey])){
			$value[$arrKey] = $default;
			ConsoleLog::error("Setting $arrKey to $default because it was empty in ".QMLog::print_r($value, true));
		}
		return $value;
	}
	/**
	 * @param mixed $itemArr
	 * @return array
	 */
	private function setDefaultsIfEmpty(array $itemArr): array {
		if(isset($itemArr['user_login']) && empty($itemArr['slug'])){
			$itemArr['slug'] = QMStr::slugify($itemArr['user_login']);
		}
//		$itemArr = $this->setDefaultIfEmpty(UserSubscriptionProviderProperty::NAME, $itemArr,
//		                                    UserSubscriptionProviderProperty::STRIPE);
		$itemArr = $this->setDefaultIfEmpty(BaseCreatedAtProperty::NAME, $itemArr,
		                                    BaseCreatedAtProperty::DEFAULT_VALUE);
		//$itemArr = UserTagTaggedUserVariableIdProperty::populateIfEmpty($itemArr);
		//$itemArr = UserTagTagUserVariableIdProperty::populateIfEmpty($itemArr);
		$itemArr = BaseSlugProperty::populateIfEmpty($itemArr);
		$itemArr = VariableCategoryControllableProperty::populateIfEmpty($itemArr);
		$itemArr = BaseIsGoalProperty::populateIfEmpty($itemArr);
		foreach($itemArr as $key => $val){
			if($val === "0000-00-00 00:00:00"){
				$itemArr[$key] = "2000-01-01 00:00:00";
			}
			if($key === Variable::FIELD_VALENCE && $val === ""){
				$itemArr[$key] = null;
			}
		}
		return $itemArr;
	}
	/**
	 * @return void
	 */
	private function updatePrimaryKeySequence(): void{
		$class = $this->getClass();
		$dest = $this->getDestinationDB();
		if($dest instanceof AbstractPostgresDB){
			try {
				$dest::updatePrimaryKeySequence(new $class);
			} catch (\Throwable $e) {
				ConsoleLog::info("Could not update sequence for $class because: ".$e->getMessage());
			}
		}
	}
	protected function getDBTable(): DBTable {
		$dbTable = DBTable::find($this->getCurrentTable());
		return $dbTable;
	}
	protected function hasColumn(string $column): bool {
		return $this->getDBTable()->hasColumn($column);
	}
	protected function getUserIdColumn(): ?string {
		return $this->getDBTable()->getUserIdColumn();
	}
}
