<?php
namespace App\Storage;
use App\Storage\DB\QMDB;
class DestinationDB extends QMDB
{
    const CONNECTION_NAME = 'destination_db';

    public static function getConnectionName(): string
    {
        return self::CONNECTION_NAME;
    }
	public static function getDefaultDBName(): string{
		return config('database.connections.' . self::getConnectionName() . '.database');
	}
	protected static function getDBDriverName(): string{
		return getenv('DESTINATION_DB_DRIVER');
	}
}
