<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Resources;

use App\Exceptions\NotEnoughMeasurementsException;
use App\Logging\ConsoleLog;
use App\Slim\View\Request\QMRequest;

trait ResourceHasCharts
{

    /**
     * @param array $arr
     * @return array
     */
    protected function addChartsOrUrl(array $arr): array{
        if($charts = $this->charts){
            $arr['charts'] = $charts;
        }
        if(!isset($arr['charts'])) {
            try {
                $arr['charts'] = $this->getChartsUrl();
            } catch (\Throwable $e) {
                ConsoleLog::exception($e);
                return $arr;
            }
            if ($this->relationLoaded('measurements') ||
                QMRequest::urlContains('charts', true)) {
                try {
                    $arr['charts'] = $this->getOrSetHighchartConfigs();
                } catch (NotEnoughMeasurementsException $e) {
                    $this->logWarning(__METHOD__ . ": " . $e->getMessage());
                }
            }
        }
        if(is_object($arr['charts'])){
            $arr['charts'] = json_decode(json_encode($arr['charts']), true);
        }
        return $arr;
    }
}
