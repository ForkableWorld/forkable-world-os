<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models\ConnectorRequests\Fitbit;
use App\DataSources\Connectors\FitbitConnector;
use App\Models\ConnectorRequests\FitbitConnectorPath;
use App\Units\MinutesUnit;
use App\VariableCategories\SleepVariableCategory;
class MinutesToFallAsleepFitbitPath extends FitbitConnectorPath {
	public const VARIABLE_NAME_MINUTES_TO_FALL_ASLEEP = 'Minutes to Fall Asleep';
	public $path = 'sleep/minutesToFallAsleep';
	public $intervalInSeconds = FitbitConnector::MAXIMUM_DATE_RANGE_FOR_SLEEP_REQUEST;
	public $unitName = MinutesUnit::NAME;
	public $variableCategoryName = SleepVariableCategory::NAME;
	public $variableName = self::VARIABLE_NAME_MINUTES_TO_FALL_ASLEEP;
	public function responseToMeasurements($response): void{
		parent::responseToMeasurements($response->{"sleep-minutesToFallAsleep"});
	}
}
