<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Measurement;
use App\Exceptions\AlreadyAnalyzingException;
use App\Exceptions\UserVariableNotFoundException;
use App\Logging\QMLog;
use App\Models\Measurement;
use App\Slim\Controller\GetController;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\View\Request\Measurement\GetMeasurementRequest;
class GetDeleteMeasurementController extends GetController {
	/**
	 * @throws UserVariableNotFoundException
	 */
	public function get(){
		$request = new GetMeasurementRequest(request()->all());
		$measurements = $request->getMeasurementsInCommonUnit();
		foreach($measurements as $measurement){
			QMLog::error("Deleting measurement", ['measurement' => $measurement]);
			QMMeasurement::writable()->where(Measurement::FIELD_ID, $measurement->id)->delete();
		}
		$v = $request->getQMUserVariable();
		try {
			$v->forceAnalyze(__FUNCTION__);
		} catch (AlreadyAnalyzingException $e) {
		}
		$v->scheduleReCorrelationDynamic("we deleted measurements");
		return $this->writeJsonWithGlobalFields(204, [
			'status' => 204,
			'success' => true,
			'message' => 'Measurement deleted successfully',
		]);
	}
}
