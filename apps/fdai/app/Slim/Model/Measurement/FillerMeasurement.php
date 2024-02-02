<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Measurement;
use App\Models\Unit;
use App\Slim\Model\QMUnit;
use App\Traits\HasModel\HasUserVariable;
use App\Variables\QMUserVariable;
class FillerMeasurement extends QMMeasurement {
	use HasUserVariable;
	/**
	 * @var Unit
	 */
	protected $unit;
	/** @noinspection PhpMissingParentConstructorInspection */
	public function __construct($timeAt, QMUserVariable $uv, QMUnit $unit){
		$this->startTime = time_or_exception($timeAt);
		$this->startAt = db_date($timeAt);
		$this->value = $uv->getFillingValueAttribute();
		$this->userVariable = $uv;
		$this->unit = $unit;
		$this->unitId = $unit->getId();
		$this->unitAbbreviatedName = $unit->getAbbreviatedName();
		$this->variableId = $uv->getVariableIdAttribute();
		$this->userId = $uv->getUserId();
	}
}
