<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models\ConnectorRequests;
use App\Variables\QMUserVariable;
abstract class SingleVariableConnectorPath extends ConnectorPath {
	public $unitName = null;
	public $variableCategoryName = null;
	public $variableName = null;
	public function getUserVariable(): QMUserVariable{
		return $this->getUserVariables()[0];
	}
	public function responseToMeasurements($response): void{
		foreach($response as $item){
			$this->addMeasurement($item);
		}
	}
	abstract public function addMeasurement($responseItem);
}
