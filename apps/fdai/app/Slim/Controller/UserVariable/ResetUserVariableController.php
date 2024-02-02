<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\UserVariable;
use App\Slim\Controller\PostController;
use App\Variables\QMUserVariable;
class ResetUserVariableController extends PostController {
	public function post(){
		$v = QMUserVariable::fromRequest(true);
		$restUserVariable = $v->resetAnalysisSettings();
		return $this->writeJsonWithGlobalFields(201, [
			'status' => 201,
			'success' => true,
			'data' => ['userVariable' => $restUserVariable],
		]);
	}
}
