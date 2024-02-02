<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral;
use App\Models\Connector;
use App\Models\ConnectorImport;
use App\Fields\BelongsTo;
use Titasgailius\SearchRelations\SearchesRelations;
class ConnectorImportBaseAstralResource extends BaseAstralAstralResource {
	use SearchesRelations;
	/**
	 * The model the resource corresponds to.
	 * @var string
	 */
	public static $model = ConnectorImport::class;
	/**
	 * The single value that should be used to represent the resource when being displayed.
	 * @var string
	 */
	public static $title = Connector::FIELD_DISPLAY_NAME;
	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [];
	/**
	 * The number of results to display in the global search.
	 * @var int
	 */
	public static $globalSearchResults = 10;
	/**
	 * The relationship columns that should be searched.
	 * @var array
	 */
	public static $searchRelations = [
		'connector' => ['name'],
	];
	public static $with = ['connector', 'user'];
	/**
	 * The per-page options used the resource index.
	 * @var array
	 */
	public static $perPageOptions = [10, 25, 50, 100];
	public static function belongsTo(string $title = null, string $relationshipMethod = null): BelongsTo{
		$field = parent::belongsTo($title, $relationshipMethod);
		return $field->searchable(true);
	}
}
