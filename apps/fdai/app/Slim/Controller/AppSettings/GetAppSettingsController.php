<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\AppSettings;
use App\AppSettings\AppSettings;
use App\AppSettings\AppSettingsResponse;
use App\AppSettings\StaticAppData;
use App\Exceptions\ClientNotFoundException;
use App\Exceptions\InvalidEmailException;
use App\Exceptions\NoEmailAddressException;
use App\Exceptions\UnauthorizedException;
use App\Models\Application;
use App\Models\Collaborator;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Controller\GetController;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
use App\Utils\Subdomain;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
class GetAppSettingsController extends GetController {
	const INCLUDE_CLIENT_SECRET = 'includeClientSecret';
	private $appSettings;
	private $userIsCollaboratorOrAdmin;
	/**
	 * @return JsonResponse|Response
	 * @throws ClientNotFoundException
	 * @throws UnauthorizedException
	 */
	public function get(){
		$r = new AppSettingsResponse();
		if(QMRequest::getInput('allBuildable')){
			if(!QMAuth::isAdmin()){
				QMAuth::throwUnauthorizedException("You must be an admin to get all configs");
			}
			$r->setAllBuildableAppSettings(Application::getAllBuildableAppSettings());
		} elseif(QMRequest::getInput('all')){
			$r->setAllAppSettings(Application::getAllWhereUserIsCollaborator());
		} elseif(QMRequest::getParam([
			'allStaticAppData',
			'staticData',
			'allStaticData',
			'includeStaticAppData',
			'includeStaticData',
		])){
			QMRequest::setMaximumApiRequestTimeLimit(90);
			$r->setStaticData($this->getAllStaticAppData());
		} else{
			$r = $this->getSettingsForOneApp($r);
		}
		return $this->writeJsonWithGlobalFields(200, $r);
	}
	/**
	 * @param AppSettingsResponse $response
	 * @return AppSettingsResponse
	 * @throws ClientNotFoundException
	 * @throws InvalidEmailException
	 * @throws NoEmailAddressException
	 * @throws UnauthorizedException
	 */
	private function getSettingsForOneApp(AppSettingsResponse $response): AppSettingsResponse{
		$this->setCacheControlHeader(5 * 60);
		if(!BaseClientIdProperty::fromRequest(true)){
			throw new BadRequestHttpException("Please provide clientId query parameter");
		}
		$appSettings = $this->appSettings ?: $this->setAppSettingsForRequestedClient();
		$response->setAppSettings($appSettings);
		//if ($this->includeClientSecret() && $this->hasClientSecretOrIsAdminOrCollaborator()) {
		// DO NOT RETURN SENSITIVE INFO WITH CLIENT SECRET BECAUSE IT IS EXPOSED IN CLIENT APPS!  ONLY USE ACCESS TOKENS
		return $response;
	}
	/**
	 * @return AppSettings
	 * @throws ClientNotFoundException
	 * @throws InvalidEmailException
	 * @throws NoEmailAddressException
	 * @throws UnauthorizedException
	 */
	private function setAppSettingsForRequestedClient(): AppSettings{
		$clientId = $this->getAppSettingsClientId();
		if(!$clientId){
			$appSettings = $this->getPhysicianAppSettingsIfNecessary();
		} else {
			$appSettings = Application::getClientAppSettings($clientId);
		}
		// Requiring includeClientSecret param reduces DB requests
		$DBModel = $appSettings->getDBModel();
		$includeSecretUsersAndCollaborators = $this->includeClientSecretOrDesignMode() || $this->getUserIsCollaboratorOrAdmin();
		if($includeSecretUsersAndCollaborators){
			$DBModel->addUserAndCollaboratorDesignModeProperties();
		} else {
			$appSettings->setClientSecret(null);
		}
		return $this->appSettings = $DBModel;
	}
	/**
	 * @return string
	 */
	private function getAppSettingsClientId(): string{
		$fallback = Subdomain::getSubDomainIfDomainIsQuantiModo(QMRequest::current());
		$clientId = $this->getClientId($fallback);
		if(in_array($clientId, BaseClientIdProperty::QUANTIMODO_ALIAS_CLIENT_IDS, true)){
			$clientId = BaseClientIdProperty::CLIENT_ID_QUANTIMODO;
		}
		return $clientId;
	}
	/**
	 * @return bool
	 */
	private function getUserIsCollaboratorOrAdmin(): bool{
		if($this->userIsCollaboratorOrAdmin === null){
			$this->userIsCollaboratorOrAdmin = Collaborator::userIsCollaboratorOrAdmin($this->getAppSettingsClientId());
		}
		return $this->userIsCollaboratorOrAdmin;
	}
	/**
	 * @return bool
	 */
	public static function includeClientSecret(): ?bool{ // Requiring includeClientSecret param reduces DB requests
		return QMRequest::getParam(self::INCLUDE_CLIENT_SECRET);
	}
	/**
	 * @return bool
	 */
	private function designMode(): bool{
		return (bool)request()->input('designMode');
	}
	/**
	 * @return bool
	 */
	private function includeClientSecretOrDesignMode(): bool{
		return GetAppSettingsController::includeClientSecret() || $this->designMode();
	}
	/**
	 * @return StaticAppData
	 */
	private function getAllStaticAppData(): StaticAppData{
		return new StaticAppData($this->getClientId());
	}
	/**
	 * @return Application|null
	 * @throws UnauthorizedException
	 * @throws InvalidEmailException
	 * @throws NoEmailAddressException
	 */
	private function getPhysicianAppSettingsIfNecessary(): ?Application{
		$u = QMAuth::getQMUser();
		if(!$u){
			return null;
		}
		$clientId = $this->getAppSettingsClientId();
		$userName = $u->getLoginName();
		$physicianClientId = $u->getPhysicianClientId();
		if($clientId === "me" || $clientId === $userName || $clientId === $physicianClientId){
			return $u->getOrCreateIndividualClientApp();
		}
		return null;
	}
}
