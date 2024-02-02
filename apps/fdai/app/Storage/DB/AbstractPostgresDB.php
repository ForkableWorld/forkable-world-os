<?php
namespace App\Storage\DB;
use App\Models\BaseModel;
abstract class AbstractPostgresDB extends QMDB {
	public const DB_PORT = 5432;
	public function getDriverName(){
		return self::DRIVER_PGSQL;
	}
	protected static function getDBDriverName(): string {
		return self::DRIVER_PGSQL;
	}
	abstract public static function getSchemaName(): string;
	public static function getConfigArray(): array{
		$arr = parent::getConfigArray();
		$arr['sslmode'] = 'prefer';
		$arr['prefix_indexes'] = true;
		$arr['database'] = static::getDbName();
		$arr['schema'] = static::getSchemaName();
		$arr['driver'] = static::getDBDriverName();
		return $arr;
	}
	/**
	 * @param BaseModel $model
	 * @return void
	 */
	public static function updatePrimaryKeySequence(BaseModel $model): void
	{
		$primaryKey = $model->getKeyName();
		$table = $model->getTable();
		$schema = static::getSchemaName();
		$sequence = $table . '_' . $primaryKey . '_seq';
		static::db()
			->statement("SELECT SETVAL('$schema.\"$sequence\"', COALESCE(MAX($schema.\"$table\".\"$primaryKey\"), 1)) FROM $schema.\"$table\";");
	}
}
