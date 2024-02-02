<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Measurement;
class GroupedQMMeasurement extends QMMeasurement {
	protected $rawMeasurementArrayIndex;
	/**
	 * GroupedMeasurement constructor.
	 * @param $groupingWidth
	 * @param $rawFilteredMeasurements
	 * @param $groupStartTime
	 */
	public function __construct($groupingWidth, $rawFilteredMeasurements, $groupStartTime){
		parent::__construct(null, null, null, $rawFilteredMeasurements[0]);
		$measurementsInGroup = [];
		$groupEndTime = $groupStartTime + $groupingWidth;
		// Loop over all measurements that haven't been added to a group yet.
		for($i = Memory::$cache['currentMeasurementGroupingIndex'], $iMax = count($rawFilteredMeasurements); $i < $iMax;
			$i++){
			if($rawFilteredMeasurements[$i]->startTime < $groupStartTime){
				continue;
			}
			if(empty($rawFilteredMeasurements[$i]->duration)){
				$rawFilteredMeasurements[$i]->duration = 0;
			}
			if($rawFilteredMeasurements[$i]->startTime + $rawFilteredMeasurements[$i]->duration >= $groupEndTime){
				break;
			}
			$measurementsInGroup[] = $rawFilteredMeasurements[$i];
		}
		$this->rawMeasurementArrayIndex = $i;
	}
}
