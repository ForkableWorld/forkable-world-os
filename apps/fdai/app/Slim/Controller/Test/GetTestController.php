<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Test;
use App\Buttons\Admin\PHPStormButton;
use App\Files\FileHelper;
use App\Slim\Controller\GetController;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
use App\Storage\Firebase\FirebaseGlobalPermanent;
use App\Storage\Firebase\FirebaseGlobalTemp;
use App\Types\QMStr;
use Illuminate\Http\RedirectResponse;
/** Class GetStudyController
 * @package App\Slim\Controller\Study
 */
class GetTestController extends GetController {
	/**
	 * @return null
	 */
	public function get(){
		if(!QMAuth::isAdminOrSendToLogin()){
			return null;
		}
		GetTestController::saveTestAndRedirectToPHPStorm();
	}
	/**
	 * @return RedirectResponse
	 */
	public static function saveTestAndRedirectToPHPStorm(): ?RedirectResponse{
		$content = QMRequest::getParam('test');
		$runConfig = QMRequest::getParam('runConfig');
		if($runConfig){
			$content = $runConfig;
		}
		if(!$content){
			$key = QMRequest::getParam('key');
			if(!$key){
				le("Could not get test content from " . FirebaseGlobalPermanent::url($key));
			}
			$content = FirebaseGlobalTemp::get($key);
			if(!$content){
				le("Could not get test content from " . FirebaseGlobalPermanent::url($key));
			}
		}
		$filename = QMStr::between($content, "class ", " extends") . '.php';
		if(QMRequest::getParam('filename')){
			$filename = QMRequest::getParam('filename');
		}
		$filename = QMStr::afterLast($filename, "/");
		if(QMRequest::getParam('download')){
			header('Content-type: text/plain');
			header('Content-Disposition: attachment; filename="' . $filename);
			echo $content;
		} else{
			$ns = QMStr::between($content, "namespace ", ";");
			$folder = FileHelper::namespaceToFolder($ns);
			$path = FileHelper::writeByDirectoryAndFilename($folder, $filename, $content);
			return PHPStormButton::redirectToFile($path, 1);
		}
	}
}
