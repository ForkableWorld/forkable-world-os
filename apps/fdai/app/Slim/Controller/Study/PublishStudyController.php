<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Study;
use App\Exceptions\AlreadyAnalyzedException;
use App\Exceptions\AlreadyAnalyzingException;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\NoUserVariableRelationshipsToAggregateException;
use App\Exceptions\UserVariableNotFoundException;
use App\Properties\UserVariable\UserVariableIsPublicProperty;
use App\Slim\Controller\PostController;
use App\Studies\QMStudy;
class PublishStudyController extends PostController {
	/**
	 * @throws NoUserVariableRelationshipsToAggregateException
	 * @throws UserVariableNotFoundException
	 * @throws AlreadyAnalyzedException
	 * @throws AlreadyAnalyzingException
	 * @throws NotEnoughDataException
	 */
	public function post(){
		$requestBody = $this->getRequestJsonBodyAsArray(false);
		$publish = UserVariableIsPublicProperty::pluckOrDefault($requestBody) ?? true;
		if($publish){
			$study = QMStudy::publishUserOrPopulationStudy();
		} else{
			QMStudy::unPublishByRequest();
		}
		return $this->writeJsonWithGlobalFields(201, [
			'status' => 'ok',
			'study' => $study ?? null,
		]);
	}
}
