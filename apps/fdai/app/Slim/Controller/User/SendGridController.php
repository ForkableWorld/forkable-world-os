<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\User;
use App\Logging\QMLog;
use App\Slim\Controller\PostController;
use App\Slim\Model\User\QMUser;
use App\Slim\QMSlim;
class SendGridController extends PostController {
	public function post(){
		$app = QMSlim::getInstance();
		$emailEvents = $app->getRequestJsonBodyAsArray(false);
		foreach($emailEvents as $emailEvent){
			if($emailEvent['event'] === "spamreport" || $emailEvent['event'] === "unsubscribe"){
				QMLog::error('Sendgrid ' . $emailEvent['event'] . ' email event leading to unsubscribe',
					['email event' => $emailEvent]);
				QMUser::unsubscribeByEmail($emailEvent['email'], $emailEvent['event']);
			}
		}
		return $this->writeJsonWithGlobalFields(201, [
			'status' => 201,
			'success' => true,
		]);
	}
}
