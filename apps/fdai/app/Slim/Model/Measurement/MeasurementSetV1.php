<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Measurement;
use App\Slim\Model\V1\MeasurementItem;
class MeasurementSetV1 {
	public const ERROR_INVALID_COMBINATION_OPERATION = 'Combination operation "%s" is invalid, must be SUM or MEAN';
	public $variableName;
	public $variableCategoryName;
	public $unitAbbreviatedName;
	public $sourceName;
	public $combinationOperation;
	public $measurementItems;
	public $parent;
	private $variable;
	private $variableParameters;
	/**
	 * Creates measurement set
	 * @param string $variableName name of this variable
	 * @param QMMeasurement[] $measurementItems array of Measurements
	 * @param string $unitAbbreviatedName name of the unit these measurements are in (abbreviated name)
	 * @param string $variableCategoryName name of the category this variable belongs in
	 * @param string $sourceName name of the source of these measurements
	 * @param string|int $combinationOperation what to do when two measurements are combined ("SUM" or "MEAN")
	 * @param array $variableParameters parameters for creating a new variable
	 * @param $variable
	 */
	public function __construct($variableName, array $measurementItems = [], $unitAbbreviatedName = null,
		$variableCategoryName = null, $sourceName = null, $combinationOperation = null, array $variableParameters = [],
		$variable = null){
		$this->variableName = $variableName;
		$this->variableCategoryName = $variableCategoryName;
		$this->unitAbbreviatedName = $unitAbbreviatedName;
		$this->sourceName = $sourceName;
		$this->combinationOperation = strtoupper($combinationOperation);
		$this->measurementItems = $measurementItems;
		$this->variableParameters = $variableParameters;
		$this->variable = $variable;
	}
}
