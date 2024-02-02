<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\AppSettings;
use App\AppSettings\AppSettings;
use App\Exceptions\BadRequestException;
use App\Exceptions\ClientNotFoundException;
use App\Exceptions\ModelValidationException;
use App\Models\Application;
use App\Models\Collaborator;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Controller\PostController;
use App\Storage\Memory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
class PostAppSettingsController extends PostController {
	/**
	 * @param array $requestBody
	 * @return array
	 * @throws \App\Exceptions\ClientNotFoundException
	 * @throws \App\Exceptions\ModelValidationException
	 */
	public static function updateAppSettings(array $requestBody): array{
		if(!$clientId = BaseClientIdProperty::pluck($requestBody)){
			throw new BadRequestException('Please provide clientId in request body');
		}
		Collaborator::authCheck($clientId);
		$updateArray = Application::updateApplication($clientId, $requestBody);
		Memory::setClientAppSettings($clientId, null);
		$responseBody =
			['appSettings' => AppSettings::getClientAppSettings($clientId)];  // Don't get outdated ones from globals
		AppSettings::deleteAppSettingsFromMemcached($clientId);
		// TODO: Implement builds again
//		$hasDesignOrSettings = isset($requestBody['appDesign']) || isset($requestBody['additionalSettings']);
//		$buildEnabled = isset($requestBody['buildEnabled']) && $requestBody['buildEnabled'];
//		if($hasDesignOrSettings || $buildEnabled){
//			$responseBody['buildTriggerResponse'] = BuildStatus::triggerBuilds($clientId);
//		}
		return $responseBody;
	}
	/**
	 * @return JsonResponse|Response
	 * @throws ClientNotFoundException
	 * @throws ModelValidationException
	 */
	public function post(){
		$requestBody = $this->getRequestJsonBodyAsArray(true);
		$appSettings = $requestBody['appSettings'] ?? $requestBody;
		if(!$appSettings){
			throw new BadRequestException("Please provide appSettings in body to update");
		}
		$responseBody = self::updateAppSettings($appSettings);
		return $this->writeJsonWithGlobalFields(201, $responseBody);
	}
}
