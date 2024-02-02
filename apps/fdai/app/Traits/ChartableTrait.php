<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Storage\DB\Migrations;
use Illuminate\Support\Str;
trait ChartableTrait {
	public static function generateMigrations(){
		$table = static::TABLE;
		$singular = Str::singular($table);
		Migrations::makeMigration("
        create table " . $singular . "_charts
(
    id               int(11) unsigned auto_increment comment 'Unique chart ID number' primary key,
    " . $singular . "_id int unsigned                        not null,
    class            varchar(140)                        not null,
    created_at       timestamp default CURRENT_TIMESTAMP not null,
    deleted_at       timestamp                           null,
    highchart_config json                                null,
    newest_data_at   timestamp                           null,
    png_generated_at timestamp                           null,
    png_url          varchar(2083)                       null,
    subtitle         varchar(2083)                       null,
    svg_generated_at timestamp                           null,
    svg_url          varchar(2083)                       null,
    title            varchar(140)                        null,
    updated_at       timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint " . $singular . "_charts_id_uindex
        unique (id),
    constraint " . $singular . "_charts_" . $table . "_id_fk
        foreign key (" . $singular . "_id) references " . $table . " (id)
);
        ", "
        create table " . $singular . "_charts
(
    id               int(11) unsigned auto_increment comment 'Unique chart ID number' primary key,
    " . $singular . "_id int unsigned                        not null,
    class            varchar(140)                        not null,
    created_at       timestamp default CURRENT_TIMESTAMP not null,
    deleted_at       timestamp                           null,
    highchart_config json                                null,
    newest_data_at   timestamp                           null,
    png_generated_at timestamp                           null,
    png_url          varchar(2083)                       null,
    subtitle         varchar(2083)                       null,
    svg_generated_at timestamp                           null,
    svg_url          varchar(2083)                       null,
    title            varchar(140)                        null,
    updated_at       timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint " . $singular . "_charts_id_uindex
        unique (id),
    constraint " . $singular . "_charts_" . $table . "_id_fk
        foreign key (" . $singular . "_id) references " . $table . " (id)
);
        ");
	}
}
