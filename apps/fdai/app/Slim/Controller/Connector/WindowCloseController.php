<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Connector;
use App\Logging\QMLog;
use App\Slim\Controller\GetController;
use App\Slim\QMSlim;
class WindowCloseController extends GetController {
	public function get(): \Illuminate\Http\Response
    {
		$msg = WindowCloseController::logNoRedirectError();
		return QMSlim::getInstance()->writeHtml(200, $msg . '<script>window.close();</script>');
	}
	/**
	 * @return string
	 */
	public static function logNoRedirectError(): string{
		$msg = 'The service has been successfully connected and an update has been scheduled';
		QMLog::error("Why are we sending to WindowCloseController and saying:\n\t'$msg'? ");
		return $msg;
	}
}
