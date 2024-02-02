<?php

namespace Database\Seeders;

use App\Logging\QMLog;
use App\Storage\DB\QMDB;
use App\Storage\DB\Writable;
use App\Storage\Memory;
use App\Utils\AppMode;
use DB;
class DatabaseSeeder extends AbstractSeeder
{
	public static function isReprocessingSeed(): bool{
		return Memory::get(Memory::REPROCESSING) ?? false;
	}
	/**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(){
		$db = static::getDB();
        QMLog::logStartOfProcess(__METHOD__);
	    $db::disableForeignKeyConstraints($db);
		$params = ['--database' => $db->getConnection()->getName()];
		$params = [];
        $this->call(WpUsersTableSeeder::class, false, $params);
        $this->call(WpUsermetaTableSeeder::class, false, $params);
        $this->call(OaClientsTableSeeder::class, false, $params);
        $this->call(ApplicationsTableSeeder::class, false, $params);
        $this->call(PhrasesTableSeeder::class, false, $params);
        $this->call(UnitsTableSeeder::class, false, $params);
        $this->call(UnitCategoriesTableSeeder::class, false, $params);
        $this->call(VariableCategoriesTableSeeder::class, false, $params);
        $this->call(VariablesTableSeeder::class, false, $params);
        $this->call(ConnectorsTableSeeder::class, false, $params);
	    $db::enableForeignKeyConstraints($db);
        if($this->isPostgres()){
	        $db->updateAllAutoIncrementSequences();
        }
        QMLog::logEndOfProcess(__METHOD__);
    }
	/**
	 * @param $tables
     * @return array
     */
    private function deleteTables($tables): array
    {
        $tryAgain = [];
        foreach ($tables as $table) {
            if(in_array($table, $this->deletedTables)){continue;}
            if(!is_string($table)) {
                $table = $table->tablename;
            }
            if ($table != 'migrations') {
                try {
                    $this->deleteTable($table);
                } catch (\Throwable $e) {
                    QMLog::info(__METHOD__.": ".$e->getMessage());
                    if(str_contains($e->getMessage(), "no such table")){
                        continue;
                    }
                    $tryAgain[] = $table;
                }
            }
        }
        return $tryAgain;
    }
    /**
     * @return void
     */
    private function deleteData(): void
    {
        AbstractSeeder::validateDB();
		try {
			$tables = DB::getSchemaBuilder()->getAllTables();
		} catch (\Throwable $e){
		    $db = DB::connection()->getName();
			$db = QMDB::find($db);
			$tables = $db->getTableNames();
		}
        $tryAgain = $this->deleteTables($tables);
        $tryAgain = $this->deleteTables($tryAgain);
        $tryAgain = $this->deleteTables($tryAgain);
        if($tryAgain) {
            le('Could not delete all tables', $tryAgain);
        }
    }

    /**
     * @param string $table
     * @return void
     */
    private function deleteTable(string $table): void
    {
        $builder = \DB::table($table);
        try {
            if($builder->count()){
                $builder->delete();
            }
        } catch (\Throwable $e) {
            Writable::disableForeignKeyConstraints(Writable::db(), $table);
            $builder->delete();
            Writable::enableForeignKeyConstraints(Writable::db(), $table);
        }
        $this->deletedTables[] = $table;
    }
	protected function isPostgres(){
		return static::getDB()->isPostgres();
	}
}
