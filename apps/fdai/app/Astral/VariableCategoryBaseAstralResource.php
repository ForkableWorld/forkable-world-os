<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral;
use App\Models\VariableCategory;
use App\Fields\BelongsTo;
class VariableCategoryBaseAstralResource extends BaseAstralAstralResource {
	/**
	 * The model the resource corresponds to.
	 * @var string
	 */
	public static $model = VariableCategory::class;
	/**
	 * The single value that should be used to represent the resource when being displayed.
	 * @var string
	 */
	public static $title = VariableCategory::FIELD_NAME;
	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [VariableCategory::FIELD_NAME];
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
	/**
	 * Indicates if the resource should be globally searchable.
	 * @var bool
	 */
	public static $globallySearchable = false;
	public function getVariableCategory(): VariableCategory{
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $this->getModel();
	}
	/**
	 * @param string|null $title
	 * @param string|null $relationshipMethod
	 * @return BelongsTo
	 */
	public static function belongsTo(string $title = null, string $relationshipMethod = null): BelongsTo{
		return parent::belongsTo($title, $relationshipMethod ?? 'variable_category')->withoutTrashed();
	}
}
