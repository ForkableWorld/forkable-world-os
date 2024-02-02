<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral;
use App\Models\BaseModel;
use App\Models\Measurement;
use Titasgailius\SearchRelations\SearchesRelations;
class MeasurementBaseAstralResource extends BaseAstralAstralResource {
	use SearchesRelations;
	/**
	 * The model the resource corresponds to.
	 * @var string
	 */
	public static $model = Measurement::class;
	/**
	 * The single value that should be used to represent the resource when being displayed.
	 * @var string
	 */
	public static $title = Measurement::FIELD_ID;
	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [];
	/**
	 * The relationship columns that should be searched.
	 * @var array
	 */
	public static $searchRelations = [
		'variable' => ['name'],
	];
	/**
	 * The relationships that should be eager loaded on index queries.
	 * @var array
	 */
	public static $with = ['variable'];
	/**
	 * Indicates if the resource should be globally searchable.
	 * @var bool
	 */
	public static $globallySearchable = false;
	/**
	 * The per-page options used the resource index.
	 * @var array
	 */
	public static $perPageOptions = [10, 25, 50, 100];
	/**
	 * The number of resources to show per page via relationships.
	 * @var int
	 */
	public static $perPageViaRelationship = 10;
	/**
	 * @return Measurement|BaseModel
	 */
	public function getMeasurement(): Measurement{
		return $this->getModel();
	}
}
