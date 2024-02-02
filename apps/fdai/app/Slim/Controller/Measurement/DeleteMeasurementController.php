<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Measurement;
use App\Exceptions\UserVariableNotFoundException;
use App\Slim\Controller\PostController;
use App\Slim\Model\Measurement\QMMeasurement;
class DeleteMeasurementController extends PostController {
	/**
	 * @throws UserVariableNotFoundException
	 */
	public function post(){
		QMMeasurement::handleDeleteRequest();
		return $this->writeJsonWithGlobalFields(204, [
			'status' => 204,
			'success' => true,
			'message' => 'Measurement deleted successfully',
		]);
	}
}
