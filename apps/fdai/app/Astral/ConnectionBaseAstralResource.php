<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral;
use App\Models\Connection;
use App\Models\Connector;
use Titasgailius\SearchRelations\SearchesRelations;
class ConnectionBaseAstralResource extends BaseAstralAstralResource {
	use SearchesRelations;
	/**
	 * The model the resource corresponds to.
	 * @var string
	 */
	public static $model = Connection::class;

	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [//'id',
	];
	public static $searchRelations = [
		'connector' => [Connector::FIELD_DISPLAY_NAME],
	];
	/**
	 * Get the searchable columns for the resource.
	 * @return array
	 */
	public static function searchableColumns(): array{
		$parent = parent::searchableColumns();
		return []; // Prevents returning id field
	}
	/**
	 * The per-page options used the resource index.
	 * @var array
	 */
	public static $perPageOptions = [10, 25, 50, 100];
	/**
	 * The relationships that should be eager loaded when performing an index query.
	 * @var array
	 */
	public static $with = [
		'user',
		'connector',
	];
}
