<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Measurement;
use App\Properties\Measurement\MeasurementNoteProperty;
use App\Slim\Controller\GetController;
use App\Slim\Middleware\QMAuth;
use App\Slim\QMSlim;
use App\Types\QMStr;
class GetNotesController extends GetController {
	public function get(){
		$this->setCacheControlHeader(5 * 60);
		$app = QMSlim::getInstance();
		$requestParams = $app->request->params();
		$requestParams = QMStr::properlyFormatRequestParams($requestParams);
		$notes = MeasurementNoteProperty::getAverageValueByNote(QMAuth::getQMUserIfSet(), $requestParams);
		return $this->writeJsonWithGlobalFields(200, [
			'success' => true,
			'data' => $notes,
		]);
	}
}
