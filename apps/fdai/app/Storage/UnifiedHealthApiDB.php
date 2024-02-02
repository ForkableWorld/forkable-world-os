<?php
namespace App\Storage;
use App\Storage\DB\QMDB;
class UnifiedHealthApiDB extends QMDB
{
    const CONNECTION_NAME = 'unified_health_api_db';

    public static function getConnectionName(): string
    {
        return self::CONNECTION_NAME;
    }

    public function getTablePrefix(): string
    {

        return 'uh_';
    }
	public static function getDefaultDBName(): string{
		return config('database.connections.' . self::getConnectionName() . '.database');
	}
	protected static function getDBDriverName(): string{
		return self::DRIVER_PGSQL;
	}
}
