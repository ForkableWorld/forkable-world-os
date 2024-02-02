<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Share;
use App\Exceptions\BadRequestException;
use App\Exceptions\NoEmailAddressException;
use App\Slim\Controller\PostController;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
class PostPatientInvitationController extends PostController {
	/**
	 * @throws NoEmailAddressException
	 */
	public function post(){
		$patientEmail = QMRequest::getParam(['email', 'emailAddress', 'patientEmail']);
		if(empty($patientEmail)){
			throw new BadRequestException("Please provide patient email");
		}
		$physician = QMAuth::getQMUser();
		$response = $physician->sendInvitationToPatient($patientEmail);
		$this->getApp()->writeJsonWithGlobalFields(201, [
			'response' => $response,
			'status' => 201,
			'success' => true,
			'code' => 201,
			'summary' => "Invited $patientEmail to share their data via email.",
			'description' => "Invited $patientEmail to share their data via email.",
		]);
	}
}
