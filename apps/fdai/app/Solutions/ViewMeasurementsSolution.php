<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
use App\Models\Measurement;
use Facade\IgnitionContracts\Solution;
class ViewMeasurementsSolution extends AbstractSolution implements Solution {
	/**
	 * @var array
	 */
	public $params;
	/**
	 * @var string|null
	 */
	private $measurementUrl;
	/**
	 * @var string
	 */
	private $fixInvalidRecordsUrl;
	/**
	 * ViewMeasurementsSolution constructor.
	 * @param array       $params
	 * @param string|null $measurementUrl
	 * @param string      $fixInvalidRecordsUrl
	 */
	public function __construct(array $params, string $measurementUrl, string $fixInvalidRecordsUrl){
		$this->params = $params;
		$this->measurementUrl = $measurementUrl;
		$this->fixInvalidRecordsUrl = $fixInvalidRecordsUrl;
	}
	public function getSolutionTitle(): string{
		return "View Measurements";
	}
	public function getSolutionDescription(): string{
		return "View Measurements and see what the deal is";
	}
	public function getDocumentationLinks(): array{
		$arr = [
			"View All Bad Measurements" => self::generateUrl($this->params),
		];
		$arr["View This Bad Measurement"] = $this->measurementUrl;
		$arr["Fix Invalid Records"] = $this->fixInvalidRecordsUrl;
		return $arr;
	}
	public static function generateUrl(array $params): string{
		return Measurement::getDataLabIndexUrl($params);
	}
}
