<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Share;
use App\Exceptions\QMException;
use App\Properties\Variable\VariableIdProperty;
use App\Slim\Controller\GetController;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\Share\ListShareRequest;
use App\Utils\APIHelper;
class GetSharesController extends GetController {
	/**
	 * Error constants.
	 */
	public const ERROR_VARIABLE_NOT_FOUND = 'Variable "%s" not found';
	/**
	 * GET /shares/:variableName
	 * List all shares for a specific variable.
	 * @SWG\Api(
	 *     path="sharing/{variableName}",
	 *     description="Get variable sharing details",
	 *     @SWG\Operations(
	 *         @SWG\Operation(
	 *             method="GET",
	 *             summary="Get variable sharing details",
	 *             notes="",
	 *             nickname="Sharing::get",
	 *             type="array",
	 *             @SWG\Items("Permission"),
	 *             @SWG\Parameters(
	 *                 @SWG\Parameter(
	 *                     name="variableName",
	 *                     description="Variable name",
	 *                     paramType="path",
	 *                     required=true,
	 *                     type="string"
	 *                 )
	 *             ),
	 *             @SWG\Authorizations(oauth2={
	 *                 {"scope": "basic", "description": "Basic scope"}
	 *             }),
	 *             @SWG\ResponseMessages(
	 *                 @SWG\ResponseMessage(code=401, message="Not authenticated")
	 *             ),
	 *             @SWG\ResponseMessages(
	 *                 @SWG\ResponseMessage(code=404, message="Unknown variable")
	 *             )
	 *         )
	 *     )
	 * )
	 */
	public function get(){
		$this->setCacheControlHeader(5 * 60);
		$clients = QMAuth::getQMUser()->getAuthorizedClients();
		$r = new ShareResponse();
		$r->setAuthorizedClients($clients);
		return $this->writeJsonWithGlobalFields(200, $r);
	}
	protected function listVariablePermissions(){
		/** @var ListShareRequest $request */
		$request = $this->getRequest();
		$variableId = VariableIdProperty::fromName($request->getVariableName());
		if($variableId === null){
			throw new QMException(QMException::CODE_NOT_FOUND, self::ERROR_VARIABLE_NOT_FOUND,
				[$request->getVariableName()]);
		}
		$permissions = Permission::getUserPermissions($request->getUserId(), $variableId);
		if(APIHelper::apiVersionIsAbove(3)){
			return $this->writeJsonWithGlobalFields(200, ['permissions' => $permissions]);
		} else{
			$this->writeJsonWithoutGlobalFields(200, $permissions);
		}
	}
}
