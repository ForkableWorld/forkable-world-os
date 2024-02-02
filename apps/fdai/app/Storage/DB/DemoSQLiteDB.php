<?php
namespace App\Storage\DB;
use Database\Seeders\WpUsermetaTableSeeder;
use Database\Seeders\WpUsersTableSeeder;
class DemoSQLiteDB extends AbstractSQLiteDB
{
    const CONNECTION_NAME = 'demo_sqlite_db';
	const DB_NAME = "C:\\code\\cd-api\\demo-data.sqlite";
    public static function getConnectionName(): string{return self::CONNECTION_NAME;}
	public static function getDefaultDBName(): string{return self::DB_NAME;}
	protected static function getDBDriverName():string{return self::DRIVER_SQLITE;}
	public function seed(){
		(new WpUsersTableSeeder())->run();
		(new WpUsermetaTableSeeder())->run();
	}
}
