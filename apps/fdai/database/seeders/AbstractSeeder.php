<?php

namespace Database\Seeders;

use App\Exceptions\NotFoundException;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Models\OAClient;
use App\Properties\Base\BaseClientIdProperty;
use App\Storage\DB\QMDB;
use App\Storage\DB\Writable;
use App\Types\QMStr;
use DB;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Seeder;

abstract class AbstractSeeder extends Seeder
{
    protected array $deletedTables = [];
    protected array $failedDeletion = [];
    public static array $alreadyMigrated = [];
    protected array $keysToUnset = [];
	/**
	 * @return BaseModel|string|null
	 */
    protected function getModelClass(): BaseModel|string|null{
        $table = $this->getTable();
        if(!$table){
            return null;
        }
        $class =  BaseModel::getClassByTable($table);
        return $class;
    }
    protected function getPrimaryKey(): string
    {
        $class = $this->getModelClass();
        return (new $class)->getPrimaryKey();
    }

    /**
     * @param string|BaseModel $class
     * @param $silent
     * @param array $parameters
     * @return AbstractSeeder
     */
    public function call($class, $silent = false, array $parameters = []): AbstractSeeder
    {
        static::$alreadyMigrated[] = $class;
        QMLog::info("Calling seeder: $class");
        AbstractSeeder::validateDB();
        $res = parent::call($class, $silent, $parameters);
        return $res;
    }
    protected function insertIfNotAllIdsInDB(array $data = null): bool
    {
	    $processName = __METHOD__ . ": insertIfNotAllIdsInDB";
        if(!$data){
            $data = $this->getData();
        }
        if(!$data){
            return false;
        }
        foreach ($data as $i => $datum) {
            if(isset($datum[BaseClientIdProperty::NAME])){
                $client = $this->findClient($datum[BaseClientIdProperty::NAME]);
                $data[$i][BaseClientIdProperty::NAME] = $client['client_id'];
            }
        }
        $qb = $this->getBuilder();
        $inDB = $qb->count();
        if($inDB === count($data)){
            $this->logInfo("Skipping because we already have $inDB rows in DB");
            return false;
        }
        if(!isset($datum)){le("no records!");}
	    QMLog::logStartOfProcess($processName);
        if($inDB > count($data)){
            $idsToAdd = collect($data)->pluck($this->getPrimaryKey())->all();
            $idsInDB = $this->getBuilder()->pluck($this->getPrimaryKey());
            $diff = $idsInDB->diff($idsToAdd);
            foreach ($diff as $idToDelete){
                $m = $this->find($idToDelete);
                $m->hardDeleteWithRelations(__METHOD__);
            }
	        QMLog::logEndOfProcess($processName);
            return true;
        }
        $res = $this->tryToInsert($data);
	    QMLog::logEndOfProcess($processName);
        return $res ?? false;
    }
    protected function getTable(): ?string
    {
        $t = str_replace("TableSeeder", "", QMStr::toShortClassName(static::class));
        $table = QMStr::snakize($t);
		$db = $this->getDB();
        if(!$db::tableExists($table)){
            return null;
        }
        return $table;
    }

    protected function findClient($clientId): ?array
    {
        $clients = OaClientsTableSeeder::getClients();
        foreach ($clients as $client){
            if(strtolower($clientId) == strtolower($client['client_id'])){
                return $client;
            }
        }
        $client = OAClient::find($clientId);
        if($client){
            return $client->toArray();
        }
        throw new NotFoundException("Client not found: {$clientId}");
    }

    protected function delete(string $table = null){
        if($table == null){
            $table = $this->getTable();
        }
        if(in_array($table, $this->deletedTables)){
            $this->logInfo("Already Deleted $table");
            return;
        }
        $builder = DB::table($table);
        if(!$builder->count()){
            $this->deletedTables[] = $table;
            return;
        }
        try {
            $builder->delete();
            $this->deletedTables[] = $table;
        } catch (\Throwable $e) {
            $this->failedDeletion[$table] = $e->getMessage();
            QMLog::info(__METHOD__.": ".$e->getMessage());
        }
    }

    /**
     * @param BaseModel $model
     * @return void
     */
    protected function updateAutoIncrement(BaseModel $model): void
    {
		$db = static::getDB();
        if($db::isPostgres()) {
	        $db::updatePrimaryKeySequence($model);
        }
    }
    /**
     * @return Builder
     */
    protected function getBuilder(): Builder
    {
        $table = $this->getTable();
        $qb = DB::table($table);
        return $qb;
    }

    protected function logInfo(string $message)
    {
        QMLog::info($this->getTable()." seeder: $message");
    }
    protected function getData():array{
        return [];
    }

    /**
     * @return void
     */
    protected function deleteAndInsert(): void
    {

        $existingRows = $this->getBuilder()->count();
        $data = $this->getData();
        if($existingRows === count($data)){
            $this->logInfo("Skipping because we already have $existingRows");
        }
        $this->delete();
        $this->insertIfNotAllIdsInDB();
    }

    protected function find($id)
    {
        $class = $this->getModelClass();
        return $class::find($id);
    }
	/**
	 * @return void
	 */
	protected static function validateDB(): void{
	    $db = static::getDB();
		$name = $db->getConfig('schema');
		if(!$name){
			$name = $db->getDatabaseName();
		}
	    if (!str_contains($name, "test") && !str_contains($name, "memory") && !str_contains($name, "sqlite")) {
	        le("You are not in a testing database. You are in: {$name}");
	    }
	}

    /**
     * @return void
     */
    protected function disableForeignKeyChecks(): void
    {
        QMDB::disableForeignKeyConstraints(static::getDB(), $this->getTable());
    }

    /**
     * @return void
     */
    protected function enableForeignKeyChecks(): void
    {
        QMDB::enableForeignKeyConstraints(static::getDB(), $this->getTable());
    }

    protected function getNewModel()
    {
        $class = $this->getModelClass();
        return new $class();
    }

    /**
     * @param array $data
     * @return bool
     */
    protected function tryToInsert(array $data): bool
    {
	    $processName = "Inserting " . count($data) . " rows into " . $this->getTable();
	    QMLog::logStartOfProcess($processName);
        foreach ($data as $i => $datum) {
            foreach ($this->keysToUnset as $key) {
                unset($data[$i][$key]);
            }
        }
        $qb = $this->getBuilder();
        try {
            $res = $qb->insert($data);
        } catch (\Throwable $e) {
            ConsoleLog::error("Failed to insert data: " . $e->getMessage());
            if (str_contains($e->getMessage(), 'VALUES lists must all be the same length')) {
                foreach ($data as $item) {
                    $res = $qb->insert([$item]);
                }
            }
            if (str_contains($e->getMessage(), 'Foreign key violation')) {
                $this->disableForeignKeyChecks();
                $res = $qb->insert($data);
                $this->enableForeignKeyChecks();
            }
        }
        $this->updateAutoIncrement($this->getNewModel());
	    QMLog::logEndOfProcess($processName);
        return $res ?? false;
    }
	protected static function getDB(){
		return Writable::db();
	}
}
