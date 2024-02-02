<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection SlowArrayOperationsInLoopInspection */
namespace App\Slim\Model\Measurement;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NoChangesException;
use App\Logging\QMLog;
use App\Models\Measurement;
use App\Models\UserVariable;
use App\Properties\Measurement\MeasurementClientIdProperty;
use App\Properties\Measurement\MeasurementDurationProperty;
use App\Properties\Measurement\MeasurementNoteProperty;
use App\Properties\Measurement\MeasurementOriginalUnitIdProperty;
use App\Properties\Measurement\MeasurementOriginalValueProperty;
use App\Properties\Measurement\MeasurementSourceNameProperty;
use App\Properties\Measurement\MeasurementStartTimeProperty;
use App\Properties\Measurement\MeasurementUnitIdProperty;
use App\Slim\Model\QMUnit;
use App\Utils\QMProfile;
use App\Variables\QMUserVariable;
class MeasurementSet {
	//private $user;  // Just get global user instead, otherwise there will be duplicates and we can use the user as a storage object
	private $userVariable;
	public $clientId;
	public $combinationOperation;
	public $connectorId;
	public $measurementItems;
	public $parent;
	public $sourceName;  // Provided source name, client id, connector name, or QMDataSource name should be in measurements.source_name column
	public $unitAbbreviatedName;
	public $unitId;
	public $userId;
	public $variableCategoryName;
	public $variableName;
	public $variableParameters;
	public const ERROR_INVALID_COMBINATION_OPERATION = 'Combination operation "%s" is invalid, must be SUM or MEAN';
	/**
	 * Creates measurement set
	 * @param string $variableName name of this variable
	 * @param QMMeasurement[] $measurementItems array of Measurements
	 * @param string|null $unitAbbreviatedName name of the unit these measurements are in (abbreviated name)
	 * @param string|null $variableCategoryName name of the category this variable belongs in
	 * @param string|null $sourceName Provided source name, client id, connector name, or QMDataSource name should be in
	 *     measurements.source_name column
	 * @param string|int $combinationOperation what to do when two measurements are combined ("SUM" or "MEAN")
	 * @param array $variableParameters parameters for creating a new variable
	 * @param null|QMUserVariable $userVariable
	 */
	public function __construct(string $variableName, array $measurementItems = [], string $unitAbbreviatedName = null,
		string $variableCategoryName = null, string $sourceName = null, $combinationOperation = null,
		array $variableParameters = [], QMUserVariable $userVariable = null){
		$this->variableName = $variableName;
		$this->variableCategoryName = $variableCategoryName;
		$this->unitAbbreviatedName = $unitAbbreviatedName;
		$this->sourceName =
			$sourceName; // Provided source name, client id, connector name, or QMDataSource name should be in measurements.source_name column
		$this->combinationOperation = strtoupper($combinationOperation);
		$this->measurementItems = $measurementItems;
		$this->variableParameters = $variableParameters;
		if($userVariable){
			$this->userVariable = $userVariable;
		}
	}
	/**
	 * @param int $userId
	 * @param MeasurementSet[]|MeasurementSet $measurementSets The measurement sets to save.
	 * @param int|null $connectorId
	 * @return int|QMUserVariable[] Number of stored results
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 * @throws NoChangesException
	 * @throws ModelValidationException
	 */
	public static function saveMeasurementSets(int $userId, $measurementSets, int $connectorId = null){
		$total = 0;
		$userVariables = [];
		$byVariable = [];
		if(!is_array($measurementSets)){$measurementSets = [$measurementSets];}
		foreach($measurementSets as $set){
			$set->userId = $userId;
			$userVariables[$set->variableName] =
			$uv = $userVariables[$set->variableName] ?? UserVariable::fromForeignData($set);
			self::validateUserVariableUnit($set, $uv);
			$items = $set->measurementItems;
			$variable = $uv->getVariable();
			$name = $variable->name;
			foreach($items as $key => $item){
				$originalUnitId = MeasurementOriginalUnitIdProperty::pluckOrDefault($set);
				if(!$originalUnitId){
					$originalUnitId = MeasurementUnitIdProperty::pluckOrDefault($set);
				}
				$originalValue = MeasurementOriginalValueProperty::pluckOrDefault($item);
				if(!$originalUnitId){
					le("Please provide originalUnitId to " . __METHOD__, $set);
				}
				$item->variableId = $uv->variable_id;
				$time = MeasurementStartTimeProperty::pluckRounded($item);
				$at = db_date($time);
				$data = $uv->newMeasurementDataByValueTime($time, $originalValue, $originalUnitId, $item);
				$data = Measurement::addConnectorInfo($data, $set);
				$data = Measurement::addLocationInfo($data, $set);
				$data = MeasurementDurationProperty::addToArrayIfPresent($data, $item);
				$data = MeasurementNoteProperty::addToArrayIfPresent($data, $item);
				$data = MeasurementSourceNameProperty::addToArrayIfPresent($data, $set);
				$data = MeasurementClientIdProperty::addToArrayIfPresent($data, $set);
				if(isset($byVariable[$name][$at])){
					QMLog::error("Skipping $name measurement at $at because we already have one in this time range",
						$data);
				} else{
					$byVariable[$name][$at] = $data;
				}
			}
		}
		foreach($byVariable as $measurements){
			Measurement::upsert($measurements);
		}
		if($connectorId){
			return $total;
		}
		return $userVariables;
	}
	/**
	 * @param MeasurementSet $set
	 * @param $uv
	 */
	private static function validateUserVariableUnit(MeasurementSet $set, $uv): void{
		if($fromSet = $set->unitAbbreviatedName){
			$fromSet = QMUnit::find($fromSet);
			$fromSet = $fromSet->abbreviatedName;
			$fromUv = $uv->getQMUnit()->abbreviatedName;
			if($fromSet && $fromUv !== $fromSet){
				le('$fromSet && $fromUv !== $fromSet');
			}
			$dbm = $uv->getDBModel();
			$fromDBM = $dbm->getUnitAbbreviatedName();
			if($fromSet && $fromDBM !== $fromSet){
				le('$fromSet && $fromDBM !== $fromSet');
			}
		}
	}
	public function getOriginalUnit(): QMUnit{
		return QMUnit::find($this->unitAbbreviatedName ?? $this->unitId);
	}
	/**
	 * @return string
	 */
	public function getVariableName(): ?string{
		return $this->variableName;
	}
	/**
	 * @param string $clientId
	 */
	public function setClientId(string $clientId): void{
		$this->clientId = $clientId;
	}
	/**
	 * @param int $connectorId
	 */
	public function setConnectorId(int $connectorId): void{
		$this->connectorId = $connectorId;
	}
}
