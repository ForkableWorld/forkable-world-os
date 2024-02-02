<?php
namespace App\Storage;
use App\Storage\DB\QMDB;
class DefaultDB extends QMDB
{
    const CONNECTION_NAME = 'pgsql';

    public static function getConnectionName(): string
    {
        return config('database.default');
    }
	public static function getDefaultDBName(): string{
		return config('database.connections.' . self::getConnectionName() . '.database');
	}
	protected static function getDBDriverName(): string{
		return config('database.connections.' . self::getConnectionName() . '.driver');
	}
}
