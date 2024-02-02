<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */
namespace App\Storage\DB;
class StagingDB extends QMDB
{
    public const CONNECTION_NAME = 'staging';
    public const DB_NAME = 'qm_staging';
    public const DB_HOST_PUBLIC = 'pg.quantimo.do';
    public const DB_HOST_PRIVATE = null;
	public const SCHEMA = 'qm_staging';
	public const DB_USER = 'postgres';
    public const DB_PORT = 5432;
	public static function getConnectionName(): string{
		return parent::CONNECTION_NAME;
	}
	public static function getDefaultDBName(): string{
		return static::DB_NAME;
	}
	public static function getPassword(): ?string{
		return \App\Utils\Env::get('STAGING_DB_PASSWORD');
	}
	protected static function getDBDriverName(): string{
		return self::DRIVER_PGSQL;
	}
}
