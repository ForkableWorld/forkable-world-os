<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Measurement;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NoChangesException;
use App\Exceptions\UnauthorizedException;
use App\Slim\Controller\PostController;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\QMSlim;
class UpdateMeasurementController extends PostController {
    /**
     * @throws ModelValidationException
     * @throws NoChangesException
     * @throws UnauthorizedException
     */
	public function post(){
		$app = QMSlim::getInstance();
		$body = $app->getRequestJsonBodyAsArray(false);
		$success = QMMeasurement::getAndUpdateMeasurement(QMAuth::id(), $body);
		return $this->writeJsonWithGlobalFields(201, [
				'status' => '201',
				'success' => $success,
			]);
	}
}
