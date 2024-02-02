<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts;
use App\Charts\QMHighcharts\ColumnHighchartConfig;
use App\Exceptions\NotEnoughMeasurementsException;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Slim\Model\Measurement\AnonymousMeasurement;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\QMUnit;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
abstract class QMMeasurementsChart extends QMChart {
	protected $labelValueArray;
	protected $seriesName;
	protected $measurements;
	protected $unitAbbreviatedName;
	/**
	 * QMColumnChart constructor.
	 * @param QMVariable|null $v
	 */
	public function __construct($v = null){
		if(!$v){
			return;
		}
		parent::__construct(null, $v);
	}
	/**
	 * @return QMMeasurement[]
	 */
	public function getInvalidMeasurements(): array{
		$variable = $this->getQMVariable();
		$measurements = $variable->getInvalidMeasurements();
		return $measurements;
	}
	/**
	 * @return QMMeasurement[]
	 * @throws NotEnoughMeasurementsException
	 */
	public function getValidDailyMeasurementsWithTagsInUserOrCommonUnit(): array{
		$variable = $this->getQMVariable();
		if($variable instanceof QMUserVariable){
			$measurements = $variable->getValidDailyMeasurementsWithTagsInUserUnit();
			if(!$measurements){
				throw new NotEnoughMeasurementsException($variable,
					"There are not enough $variable->name " . "measurements to generate charts. ");
			}
			return $measurements;
		}
		if($variable instanceof QMCommonVariable){
			$measurements = $variable->getValidDailyMeasurementsWithTags();
			if(!$measurements){
				$t = $this->getTitleAttribute();
				$invalidMeasurements = $variable->getInvalidMeasurements();
				$invalidCount = count($invalidMeasurements);
				throw new NotEnoughMeasurementsException($variable,
					"There are not enough $variable->name " . " valid measurements to generate $t charts. 
					There are $invalidCount invalid measurements. ");
			}
			return $measurements;
		}
		le("Variable not user or common!");
	}
	/**
	 * @return string
	 */
	public function getUserUnitAbbreviatedName(): string{
		try {
			$measurements = $this->getValidDailyMeasurementsWithTagsInUserOrCommonUnit();
			$m = AnonymousMeasurement::getFirst($measurements);
			if(isset($m->unitAbbreviatedName)){
				return $m->unitAbbreviatedName;
			}
		} catch (NotEnoughMeasurementsException $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
		}
		return $this->getQMVariable()->getUnitAbbreviatedName();
	}
	/**
	 * @return QMUnit
	 */
	public function getUserUnit(): QMUnit{
		return QMUnit::find($this->getUserUnitAbbreviatedName());
	}
	/**
	 * @return QMVariable
	 */
	public function getQMVariable(): QMVariable{
		$v = $this->getSourceObject();
		if($v instanceof BaseModel){
			return $v->getDBModel();
		}
		return $v;
	}
	/**
	 * @param \Exception|NotEnoughMeasurementsException $e
	 * @param ColumnHighchartConfig $config
	 * @return ColumnHighchartConfig
	 */
	protected function setSubTitleFromInvalidMeasurements(\Exception|NotEnoughMeasurementsException $e,
	                                                   ColumnHighchartConfig                     $config): ColumnHighchartConfig{
		$variable = $this->getQMVariable();
		$measurements = $variable->getInvalidMeasurements();
		$message = $e->getMessage();
		$message .= count($measurements)." invalid measurements were excluded. ";
		$config->setSubtitle(__METHOD__.": ".$e->getMessage());
		return $config;
	}
}
