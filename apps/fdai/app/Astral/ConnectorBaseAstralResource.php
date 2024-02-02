<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral;
use App\Models\Connector;
use App\Fields\BelongsTo;
class ConnectorBaseAstralResource extends BaseAstralAstralResource {
	/**
	 * The model the resource corresponds to.
	 * @var string
	 */
	public static $model = Connector::class;
	/**
	 * The single value that should be used to represent the resource when being displayed.
	 * @var string
	 */
	public static $title = Connector::FIELD_DISPLAY_NAME;
	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [
		Connector::FIELD_DISPLAY_NAME,
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
	public static $searchRelations = [];
	/**
	 * The per-page options used the resource index.
	 * @var array
	 */
	public static $perPageOptions = [100];
	public static function belongsTo(string $title = null, string $relationshipMethod = null): BelongsTo{
		$field = parent::belongsTo($title, $relationshipMethod);
		return $field->searchable(true);
	}
}
