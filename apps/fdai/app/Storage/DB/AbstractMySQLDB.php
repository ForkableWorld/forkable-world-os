<?php
namespace App\Storage\DB;
abstract class AbstractMySQLDB extends QMDB {
	public const DB_PORT = 3306;
	protected static function getDBDriverName(): string {
		return self::DRIVER_MYSQL;
	}
}
