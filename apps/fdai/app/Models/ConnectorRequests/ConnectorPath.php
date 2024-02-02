<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models\ConnectorRequests;
use App\DataSources\QMConnector;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\NoChangesException;
use App\Models\ConnectorRequest;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Variables\QMUserVariable;
abstract class ConnectorPath {
	public $QMConnector;
	public $path;
	public $intervalInSeconds = 86400;
	public function __construct(QMConnector $connector){
		$this->QMConnector = $connector;
	}
	/**
	 * @return QMConnector
	 */
	public function getQMConnector(): QMConnector{
		return $this->QMConnector;
	}
	/**
	 * @param string $fromAt
	 * @return QMMeasurement[]
	 */
	public function alreadyHaveMeasurements(string $fromAt): ?array{
		$currentEndAt = $this->generateCurrentEndAt($fromAt);
		$variables = $this->getUserVariables();
		foreach($variables as $variable){
			/** @var QMUserVariable $variable */
			$measurements = $variable->getMeasurementsBetween($fromAt, $currentEndAt);
			$forConnector = collect($measurements)->where('connectorId', $this->getQMConnector()->id)->all();
			if($forConnector){
				return $forConnector;
			}
		}
		return null;
	}
	public function alreadyMadeRequest(string $url): ?ConnectorRequest{
		$c = $this->getQMConnector();
		$requests = $c->getConnectorRequests();
		foreach($requests as $request){
			if($request->uri === $url){
				return $request;
			}
		}
		return null;
	}
	abstract public function generateUrl(string $fromAt): string;
	public function getUrl(array $params): string{
		return $this->getQMConnector()->getUrlForPath($this->path, $params);
	}
	/**
	 * @return void
	 */
	public function import(){
		$currentFromAt = $absoluteFromAt = $this->getAbsoluteFromAt();
		$absoluteEndAt = $this->generateAbsoluteEndAt();
		$c = $this->getQMConnector();
		while($currentFromAt < $absoluteEndAt){
			$currentEndAt = $this->generateCurrentEndAt($currentFromAt);
			$existingMeasurements = $this->alreadyHaveMeasurements($currentFromAt);
			if($existingMeasurements){
				return null;
			}
			$url = $this->generateUrl($currentFromAt);
			$existingRequest = $this->alreadyMadeRequest($url);
			if($existingRequest){
				return null;
			}
			$response = $c->getRequest($url);
			$this->responseToMeasurements($response);
			$currentFromAt = db_date(strtotime($currentEndAt) + 86400);
		}
		$this->saveMeasurements();
	}
	public function getAbsoluteFromAt(): string{
		$c = $this->getQMConnector();
		$absoluteMin = $c->getFromTime();
		return db_date(86400 * round($absoluteMin / 86400));
	}
	private function generateAbsoluteEndAt(): string{
		$yesterday = time() - 86400;
		$rounded = round($yesterday / 86400) * 86400;
		return db_date($rounded);
	}
	/**
	 * @param $response
	 * @return array
	 */
	abstract public function responseToMeasurements($response): void;
	/**
	 * @return QMUserVariable[]
	 */
	public function getUserVariables(): array{
		$c = $this->getQMConnector();
		$arr[] = $c->getQMUserVariable($this->variableName, $this->unitName, $this->variableCategoryName);
		return $arr;
	}
	/**
	 * @param $intOrString
	 * @return mixed
	 */
	public function formatDate($intOrString){
		return $this->getQMConnector()->formatDate($intOrString);
	}
	public function generateCurrentEndAt(string $fromAt): string{
		$current = strtotime($fromAt) + $this->intervalInSeconds;
		$abs = strtotime($this->generateAbsoluteEndAt());
		if($abs < $current){
			$current = $abs;
		}
		return db_date($current);
	}
	/**
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 * @throws NoChangesException
	 */
	private function saveMeasurements(){
		$variables = $this->getUserVariables();
		foreach($variables as $v){
			$v->saveMeasurements($this->getQMConnector()->id);
		}
	}
}
