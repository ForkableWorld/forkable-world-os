<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\DeleteMethods\Measurement;
use App\Exceptions\UserVariableNotFoundException;
use App\Slim\Controller\DeleteController;
use App\Slim\Model\Measurement\QMMeasurement;
class DeleteMeasurementController extends DeleteController {
	/**
	 * @throws UserVariableNotFoundException
	 */
	public function delete(){
		QMMeasurement::handleDeleteRequest();
		return $this->writeJsonWithGlobalFields(204, [
			'status' => 204,
			'success' => true,
			'message' => 'Measurement deleted successfully',
		]);
	}
}
