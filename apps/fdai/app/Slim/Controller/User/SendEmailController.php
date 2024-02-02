<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\User;
use App\Exceptions\QMException;
use App\Mail\QMSendgrid;
use App\Slim\Controller\PostController;
use App\Slim\Middleware\QMAuth;
use LogicException;
class SendEmailController extends PostController {
	public function post(){
		$app = $this->getApp();
		$requestBody = $app->getRequestJsonBodyAsArray(false);
		$mailChimpListIds = [
			'couponInstructions' => '1e8e5c08b8',
			'quantimodoChromeLink' => 'a6c28be183',
			'postStudyJoin' => '83b517aed3',
		];
		if(!isset($requestBody['emailType'])){
			throw new QMException(QMException::CODE_BAD_REQUEST, 'Please provide emailType in body');
		}
		if(!isset($mailChimpListIds[$requestBody['emailType']])){
			throw new QMException(QMException::CODE_BAD_REQUEST,
				'Please provide valid emailType.  Options are ' . array_keys($mailChimpListIds));
		}
		$success =
			QMSendgrid::addUserToMailChimpList(QMAuth::getQMUserIfSet(), $mailChimpListIds[$requestBody['emailType']]);
		if($success){
			return $this->writeJsonWithGlobalFields(201, [
				'status' => 201,
				'success' => true,
			]);
		} else{
			throw new LogicException('Could not add user to mailing list');
		}
	}
}
