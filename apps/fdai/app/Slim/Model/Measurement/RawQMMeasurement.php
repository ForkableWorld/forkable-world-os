<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Measurement;
use App\Slim\Model\QMUnit;
use App\Slim\View\Request\Measurement\GetMeasurementRequest;
use App\Variables\QMUserVariable;
class RawQMMeasurement extends QMMeasurement {
	/**
	 * RawMeasurement constructor.
	 * @param $row
	 * @param GetMeasurementRequest|array $req
	 * @param QMUserVariable $uv
	 */
	public function __construct($row, GetMeasurementRequest $req, QMUserVariable $uv, QMUnit $unit){
		$this->userVariable = $uv;
		parent::__construct(null, null, null, $row);
		if($uv){
			if(!$this->unitAbbreviatedName){
				$this->unitAbbreviatedName = $unit->abbreviatedName;
			}
			if(!$this->variableCategoryId){
				$this->variableCategoryId = $uv->variableCategoryId;
			}
			if(!$this->variableId){
				$this->variableId = $uv->variableId;
			}
			if(!$this->variableName){
				$this->variableName = $uv->variableName;
			}
		}
		$this->setValueWithRequest($req, $uv);
		if(!isset($this->unitId) && $uv){
			$this->unitId = $uv->getCommonUnitId();
		}
	}
	public function populateFast($data, $variable, $unit){
	}
	/**
	 * @param array|GetMeasurementRequest $req
	 * @param QMUserVariable $userVariable
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpUnhandledExceptionInspection
	 */
	private function setValueWithRequest($req, $userVariable = null){
		if(is_array($req)){
			$req = new GetMeasurementRequest($req);
		}
		if(!$req->getDoNotConvert()){
			if($req->getRequestedUnit()){
				$this->value = QMUnit::convertValueByUnitIds($this->value, $this->unitId, $req->getRequestedUnit()->id,
					$this->getQMVariable());
				$this->setUnitId($req->getRequestedUnit()->id);
			} elseif($req->getUserUnit()){
				$this->value = QMUnit::convertValueByUnitIds($this->value, $this->unitId, $req->getUserUnit()->id,
					$this->getQMVariable());
				$this->setUnitId($this->$req->getUserUnit()->id);
			} elseif(isset($userVariable)){
				$this->value = QMUnit::convertValueByUnitIds($this->value, $this->unitId,
					$userVariable->getUnitIdAttribute(), $this->getQMVariable());
				$this->setUnitId($userVariable->getUnitIdAttribute());
			} elseif($this->userUnitId && $this->userUnitId !== $this->unitId){
				$this->value = QMUnit::convertValueByUnitIds($this->value, $this->unitId, $this->userUnitId,
					$this->getQMVariable());
				$this->setUnitId($this->userUnitId);
			}
			if($this->unitId === 10){
				$this->value = round($this->value);
			} // Need to round /5 measurements so we don't break MoodiModo android sync
		}
	}
}
