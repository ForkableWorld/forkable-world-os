<?php
namespace App\Storage\DB;
use App\Logging\QMLog;
use Illuminate\Database\QueryException;
abstract class AbstractSQLiteDB extends QMDB {
	public function getDriverName(){
		return self::DRIVER_SQLITE;
	}
	public static function tableExists(string $table): bool{
		try {
			static::getDBTable($table)->qb()->first();
			return true;
	} catch (QueryException $e){
			QMLog::info("Table $table does not exist in ".static::getConnectionName().". ".$e->getMessage());
		    return false;
		}
	}
	abstract public static function getConnectionName(): string;
	/**
	 * @return string[]
	 */
	public static function getTableNames(): array{
		$tables = [];
		$connection = static::getConnection();
		$statement = $connection->getPdo()->prepare("SELECT name FROM sqlite_master WHERE type='table'");
		$statement->execute();
		$results = $statement->fetchAll(\PDO::FETCH_ASSOC);
		foreach($results as $result){
			$tables[] = $result['name'];
		}
		return $tables;
	}
	/**
	 * Get all of the table names for the database.
	 *
	 * @return array
	 */
	public function getAllTables()
	{
		$res = $this->statement("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
		return $res;
	}

}
