<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasMany;
use App\DataSources\Connectors\FitbitConnector;
use App\DataSources\Connectors\QuantiModoConnector;
use App\DataSources\QMDataSource;
use App\Exceptions\ModelValidationException;
use App\Models\Variable;
use App\Properties\Base\BaseNumberOfRawMeasurementsWithTagsJoinsChildrenProperty;
use App\Properties\Variable\VariableDataSourcesCountProperty;
use App\Slim\Model\Measurement\MeasurementExportRequest;
use App\Types\ObjectHelper;
trait HasManyMeasurements {
	/**
	 * @return string
	 */
	public function getMeasurementQuantitySentence(): string{
		$sentence = strtoupper($this->getDisplayNameAttribute()) . " Data Quantity:\n";
		$numberTagged = $this->getOrCalculateNumberOfRawMeasurementsWithTagsJoinsChildren();
		if(!$numberTagged){
			$sentence .= $this->getNumberOfTaggedMeasurementsSentence();
		} else{
			$sentence .= $this->getNumberOfRawMeasurementsSentence() . "\n";
			$sentence .= $this->getNumberOfTaggedMeasurementsSentence() . "\n";
		}
		if($numberTagged){
			$sentence .= $this->getNumberOfChangesSentence();
		}
		return $sentence;
	}
	/**
	 * @return string
	 */
	public function getNumberOfTaggedMeasurementsSentence(): string{
		$numberTagged = $this->getOrCalculateNumberOfRawMeasurementsWithTagsJoinsChildren();
		$sentence = "There are $numberTagged $this->name measurements when you include those derived from tagged " .
			"duplicate, child or ingredient variables and those derived from zero filling gaps in data.\n";
		return $sentence;
	}
	/**
	 * @return string
	 */
	public function getNumberOfChangesSentence(): string{
		$sentence = "There " . $this->getNumberOfChanges() . " changes spanning " .
			$this->getNumberOfDaysBetweenEarliestAndLatestTaggedMeasurement() . " days from " .
			$this->getEarliestTaggedMeasurementDate() . " to " . $this->getLatestTaggedMeasurementDate() . ".\n";
		return $sentence;
	}
	/**
	 * @return string
	 */
	public function getNumberOfRawMeasurementsSentence(): string{
		$number = $this->getOrCalculateNumberOfMeasurements();
		$sentence = "There are $number raw $this->name measurements. ";
		return $sentence;
	}
	/**
	 * @return int
	 */
	public function getOrCalculateNumberOfRawMeasurementsWithTagsJoinsChildren(): int{
		$number = $this->getAttribute(Variable::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN);
		if($number === null){
			$number = $this->calculateNumberOfRawMeasurementsWithTagsJoinsChildren();
			if($number !== null){
				$number = $this->getAttribute(Variable::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN);
				try {
					$this->save();
				} catch (ModelValidationException $e) {
					le($e);
				}
			}
		}
		return $number;
	}
	/**
	 * @return int
	 */
	public function calculateNumberOfRawMeasurementsWithTagsJoinsChildren(): int{
		return BaseNumberOfRawMeasurementsWithTagsJoinsChildrenProperty::calculate($this);
	}
	/**
	 * @param array $urlParams
	 * @return string
	 */
	public function getTrackingInstructionsHtml(array $urlParams = []): string{
		$sources = [];
		$best = $this->getBestDataSource();
		$sources[$best->getId()] = $best;
		if($this->manualTracking){
			$manual = QuantiModoConnector::instance();
			$sources[$manual->id] = $manual;
		}
		$connectors = $this->getDataSources();
		foreach($connectors as $connector){
			if(!$connector->isCrappyOrDisabled()){
				$sources[$connector->getId()] = $connector;
			}
		}
		$html = '';
		foreach($sources as $source){
			$html .= $source->setInstructionsHtml($this, $urlParams);
		}
		return $html;
	}
	/**
	 * @return QMDataSource
	 */
	public function getBestDataSource(): QMDataSource{
		$dataSource = $this->getMostCommonAffiliatedConnector();
		if(!$dataSource || $dataSource->isCrappyOrDisabled()){
			$dataSource = $this->getMostCommonAffiliatedDataSource();
		}
		if(ObjectHelper::isMongoOrStdClass($dataSource)){
			$dataSource = QMDataSource::getDataSourceWithoutDBQuery($dataSource->name);
		}
		if(!$dataSource || $dataSource->isCrappyOrDisabled()){
			$dataSource = $this->getMostCommonConnector();
		}
		if(ObjectHelper::isMongoOrStdClass($dataSource)){
			$dataSource = QMDataSource::getDataSourceWithoutDBQuery($dataSource->name);
		}
		if(!$dataSource || $dataSource->isCrappyOrDisabled()){
			$dataSource = QMDataSource::getQuantiModoDataSource();
		}
		if(ObjectHelper::isMongoOrStdClass($dataSource)){
			$dataSource = QMDataSource::getDataSourceWithoutDBQuery($dataSource->name);
		}
		if($dataSource && $dataSource->getNameAttribute() === 'jawbone' || $dataSource->getNameAttribute() === "up"){
			$dataSource = QMDataSource::getDataSourceWithoutDBQuery(FitbitConnector::NAME);
		}
		return $dataSource;
	}
	/**
	 * @return QMDataSource
	 */
	public function getMostCommonAffiliatedDataSource(): ?QMDataSource{
		$mostCommon = null;
		$maxCount = -1;
		$dataSources = $this->getDataSources();
		foreach($dataSources as $dataSource){
			if(!$dataSource->count){
				$dataSource->count = 1;
			}
			if($dataSource->affiliate && $dataSource->count > $maxCount){
				$mostCommon = $dataSource;
				$maxCount = $dataSource->count;
			}
		}
		if(ObjectHelper::isMongoOrStdClass($mostCommon)){
			$mostCommon = QMDataSource::getDataSourceWithoutDBQuery($mostCommon->name);
		}
		if($mostCommon){
			return $mostCommon->getPlatformAgnosticDataSource();
		}
		return $mostCommon;
	}
	/**
	 * @return QMDataSource
	 */
	public function getMostCommonAffiliatedConnector(): ?QMDataSource{
		$mostCommon = null;
		$maxCount = -1;
		$dataSources = $this->getDataSources();
		foreach($dataSources as $dataSource){
			if(!$dataSource->count){
				$dataSource->count = 1;
			}
			if($dataSource->count > $maxCount && $dataSource->affiliate && $dataSource->isConnector()){
				$mostCommon = $dataSource;
				$maxCount = $dataSource->count;
			}
		}
		if(ObjectHelper::isMongoOrStdClass($mostCommon)){
			$mostCommon = QMDataSource::getDataSourceWithoutDBQuery($mostCommon->name);
		}
		return $mostCommon;
	}
	/**
	 * @return QMDataSource
	 */
	public function getMostCommonAffiliatedDataSourceOrQuantiModo(): QMDataSource{
		$dataSource = $this->getMostCommonAffiliatedDataSource();
		if($dataSource){
			return $dataSource;
		}
		return QMDataSource::getDataSourceWithoutDBQuery('QuantiModo');
	}
	/**
	 * Provided source name, client id, connector name, or QMDataSource name should be in measurements.source_name
	 * column
	 * @return string[]
	 */
	protected function getOrCalculateDataSourcesCount(): array{
		if(!$this->getDataSourcesCount() && $this->getNumberOfMeasurements()){
			$this->calculateDataSourcesCount();
		}
		return $this->getDataSourcesCount();
	}
	/**
	 * @return QMDataSource[]
	 */
	public function getDataSources(): array{
		if(property_exists($this, 'dataSourcesCount')){
			$arr = $this->dataSourcesCount;
		} else {
			$arr = $this->data_sources_count;
		}
		$dataSources = VariableDataSourcesCountProperty::convertDataSourcesCountToDataSources($arr);
		return array_values($dataSources);
	}
	/**
	 * @return QMDataSource
	 */
	public function getMostCommonConnector(): ?QMDataSource{
		$c = null;
		$QMDataSources = $this->getDataSources();
		if($QMDataSources && !$this->getMostCommonConnectorId()){
			$most = 0;
			foreach($QMDataSources as $dataSource){
				if($dataSource->count > $most && $dataSource->isConnector()){
					if(ObjectHelper::isMongoOrStdClass($dataSource)){
						$dataSource = QMDataSource::getDataSourceWithoutDBQuery($dataSource->name);
					}
					$c = $dataSource;
				}
			}
			if(ObjectHelper::isMongoOrStdClass($c)){
				$c = QMDataSource::getDataSourceWithoutDBQuery($c->name);
			}
		}
		if(!$c && $this->getMostCommonConnectorId()){
			$c = QMDataSource::getDataSourceWithoutDBQuery($this->getMostCommonConnectorId());
		}
		return $c ?: null;
	}
	/**
	 * @return int
	 */
	public function getOrCalculateNumberOfMeasurements(): int{
		$raw = $this->getAttribute(Variable::FIELD_NUMBER_OF_MEASUREMENTS);
		if($raw === null){
			return $this->calculateNumberOfMeasurements();
		}
		return $raw;
	}
	/**
	 * @return int
	 */
	public function getNumberOfDaysBetweenEarliestAndLatestTaggedMeasurement(): int{
		$latestAt = $this->getLatestTaggedMeasurementAt();
		if(!$latestAt){
			$latestAt = $this->getLatestNonTaggedMeasurementStartAt();
		}
		if(!$latestAt){
			$latestAt = $this->calculateLatestTaggedMeasurementAt();
		}
		$earliestAt = $this->getEarliestTaggedMeasurementAt();
		if(!$earliestAt){
			$earliestAt = $this->getEarliestNonTaggedMeasurementStartAt();
		}
		if(!$earliestAt){
			$earliestAt = $this->calculateEarliestTaggedMeasurementAt();
		}
		return round((time_or_exception($latestAt) - time_or_exception($earliestAt)) / 86400);
	}
    public function getCsv(): string {
        $measurements = $this->getMeasurements();
        $path = $this->getSlugWithNames()."-measurement-export.csv";
        MeasurementExportRequest::exportMeasurementsToCsv($path, $measurements);
        return $path;
    }
}
