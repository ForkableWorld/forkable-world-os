<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Variable;
use App\Slim\Controller\GetController;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
use App\Variables\QMCommonVariable;
class GetDeleteVariableController extends GetController {
	public function get(){
		QMAuth::isAdminOrException();
		$id = QMRequest::getParam('variableId');
		$reason = QMRequest::getParam('reason');
		$hard = QMRequest::getParam('hardDelete');
		QMCommonVariable::deleteRelatedRecords($id, $hard, $reason);
		return $this->writeJsonWithGlobalFields(200, []);
	}
}
