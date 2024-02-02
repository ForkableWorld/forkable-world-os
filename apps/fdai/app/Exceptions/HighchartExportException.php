<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Solutions\ViewAnalyzableDataSolution;
use App\Charts\QMChart;
class HighchartExportException extends BaseException {
    /**
     * @var QMChart
     */
    public $chart;
    /**
     * ChartGenerationException constructor.
     * @param string $message
     * @param QMChart $chart
     */
    public function __construct(string $message, QMChart $chart) {
        $this->chart = $chart;
        parent::__construct($message);
    }
    public function getSolution(): \Facade\IgnitionContracts\Solution{
        return new ViewAnalyzableDataSolution($this->getChart()->getSourceObject());
    }
    /**
     * @return QMChart
     */
    public function getChart(): QMChart{
        return $this->chart;
    }
}
