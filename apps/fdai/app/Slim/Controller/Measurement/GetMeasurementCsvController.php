<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Measurement;
use App\Exceptions\WrongUnitException;
use App\Slim\Controller\GetController;
use App\Slim\View\Request\Measurement\GetMeasurementRequest;
class GetMeasurementCsvController extends GetController {
	/**
	 * @throws WrongUnitException
	 */
	public function get(){
		$this->setCacheControlHeader(5 * 60);
		/** @var GetMeasurementRequest $request */
		$request = $this->getRequest();
		$uv = $request->getQMUserVariable();
		$measurements = $uv->getQMMeasurements();
		$header[] = 'Event Start Time';
		$header[] = 'Variable Name';
		$header[] = 'Value';
		$header[] = 'Unit';
		$header[] = 'Note';
		$header[] = 'Source';
		$filename = "$uv->name Measurements from QuantiModo.txt";
		header('Content-Type: application/csv');
		header('Content-Disposition: attachment; filename="' . $filename . '";');
		// open raw memory as file so no temp files needed, you might run out of memory though
		$fp = fopen('php://output', 'wb');
		fputcsv($fp, $header, "\t");
		if(is_array($measurements)){
			foreach($measurements as $m){
				$csvArray = null;
				$csvArray[] = gmdate('Y-m-d', $m->startTime);
				$csvArray[] = $uv->name;
				$csvArray[] = $m->value;
				$csvArray[] = $m->getUnitAbbreviatedName();
				$csvArray[] = $m->note;
				$csvArray[] = $m->sourceName;
				fputcsv($fp, $csvArray, "\t");
			}
		} else{
			return $this->writeJsonWithGlobalFields(200, ['success' => true,]);
		}
	}
}
