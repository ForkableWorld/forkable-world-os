<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models\ConnectorRequests;
use App\DataSources\Connectors\FitbitConnector;
use App\DataSources\QMConnector;
use App\Slim\Model\Measurement\QMMeasurement;
abstract class FitbitConnectorPath extends SingleVariableConnectorPath {
	public $intervalInSeconds = FitbitConnector::MAXIMUM_DATE_RANGE_FOR_REQUEST;
	public function generateUrl(string $fromAt): string{
		$startDate = $this->formatDate($fromAt);
		$currentEndAt = $this->generateCurrentEndAt($fromAt);
		$endDate = $this->formatDate($currentEndAt);
		$url = FitbitConnector::$BASE_API_URL . '/1/user/-/' . $this->path . '/date/' . $startDate . '/' . $endDate .
			'.json';
		return $url;
	}
	/**
	 * @return FitbitConnector
	 */
	public function getQMConnector(): QMConnector{
		return parent::getQMConnector();
	}
	/**
	 * @param $item
	 */
	public function addMeasurement($item): void{
		$v = $this->getUserVariable();
		$m = new QMMeasurement($item->dateTime, $item->value);
		$m->setOriginalUnitByNameOrId($this->unitName);
		$v->addToMeasurementQueueIfNoneExist($m);
	}
}
