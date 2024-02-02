<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage\DB;
use Illuminate\Support\Collection;
class ClinicalTrialsDB extends AbstractPostgresDB {
    public const CONNECTION_NAME = 'clinical_trials';
    public const DB_NAME = 'aact';
    public const DB_HOST_PUBLIC = 'aact-db.ctti-clinicaltrials.org';
    public const DB_PORT = 5432;
    const DB_SCHEMA = 'ctgov';

    public static function getConnectionName(): string{return static::CONNECTION_NAME;}
	public static function getDefaultDBName(): string{return static::DB_NAME;}
    public static function getPassword(): string{return 'XBW4TK5ec3BgnzZ';}
    /**
     * @param string $query
     * @return Collection
     */
    public static function getStudiesLike(string $query){
        $studies = ClinicalTrialsDB::getBuilderByTable('studies')
            ->where('official_title', \App\Storage\DB\ReadonlyDB::like(), '%'.$query.'%')
            ->get();
        return $studies;
    }

	public static function getSchemaName(): string{return static::DB_SCHEMA;}
}
