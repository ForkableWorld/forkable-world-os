<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral;
use App\Models\ConnectorRequest;
use App\Fields\BelongsTo;
use Titasgailius\SearchRelations\SearchesRelations;
class ConnectorRequestBaseAstralResource extends BaseAstralAstralResource {
	use SearchesRelations;
	/**
	 * The model the resource corresponds to.
	 * @var string
	 */
	public static $model = ConnectorRequest::class;

	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [
		'id',
	];
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
	public static function belongsTo(string $title = null, string $relationshipMethod = null): BelongsTo{
		$field = parent::belongsTo($title, $relationshipMethod);
		return $field->searchable(true);
	}
}
