<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Connector;
use App\Buttons\States\ImportStateButton;
use App\DataSources\QMConnector;
use App\Exceptions\UnauthorizedException;
use App\Http\Urls\IntendedUrl;
use App\Logging\QMLog;
use App\Slim\Controller\GetController;
use App\Slim\Model\Notifications\PusherPushNotification;
use App\Utils\UrlHelper;
use Illuminate\Http\RedirectResponse;
use Pusher\PusherException;
class ConnectionFinishController extends GetController {
	/**
	 * @return RedirectResponse
	 */
	public function get(){
		$url = IntendedUrl::get();
		if(!$url){
			$url = ImportStateButton::url();
			QMLog::error("No FinalCallbackUrl so sending to $url");
		}
		if($url && str_starts_with($url, 'chrome-extension://')){  // We can't send to chrome-extension
			WindowCloseController::logNoRedirectError();
			$url = QMConnector::getWindowCloseUrl();
		}
		try {
			PusherPushNotification::sendUserViaPushForIFrameAuth();
		} catch (PusherException $e) {
			QMLog::error("PusherException: " . $e->getMessage());
		}
		try {
			$r = QMConnector::addParamsToUrlAndRedirect($url, 'accessToken');
		} catch (UnauthorizedException $e) {
			QMLog::error("UnauthorizedException: " . $e->getMessage());
		}
		return UrlHelper::redirect($r->getTargetUrl(), $r->getStatusCode());
	}
}
