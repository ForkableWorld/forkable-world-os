<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral;
use App\Models\Variable;
use App\Fields\BelongsTo;
class VariableBaseAstralResource extends BaseAstralAstralResource {
	/**
	 * Indicates if the resource should be globally searchable.
	 * @var bool
	 */
	public static $globallySearchable = true;
	/**
	 * The model the resource corresponds to.
	 * @var string
	 */
	public static $model = Variable::class;
	/**
	 * The single value that should be used to represent the resource when being displayed.
	 * @var string
	 */
	public static $title = Variable::FIELD_NAME;
	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [
		Variable::FIELD_NAME,
	];
	/**
	 * The relationships that should be eager loaded when performing an index query.
	 * @var array
	 */
	public static $with = [
		'default_unit',
		'variable_category',
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
	public static $perPageOptions = [10, 25, 50, 100];
	/**
	 * The number of resources to show per page via relationships.
	 * @var int
	 */
	public static $perPageViaRelationship = 20;
	public static function belongsTo(string $title = null, string $relationshipMethod = null): BelongsTo{
		$field = parent::belongsTo($title, $relationshipMethod);
		return $field->searchable(true);
	}
}
