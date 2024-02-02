<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Connector;
use App\DataSources\QMConnector;
use App\Exceptions\UnauthorizedException;
use App\Slim\Controller\GetController;
use App\Slim\Middleware\QMAuth;
use App\Utils\APIHelper;
use Illuminate\Http\JsonResponse;
class GetConnectorsController extends GetController {
	/**
	 * @return \Illuminate\Http\Response|JsonResponse
	 * @throws \App\Exceptions\UnauthorizedException
	 */
	public function get(){
		$sources = GetConnectorsController::getDataSources();
		$sources = array_values($sources); // Sometimes this gets decoded as an object so trying array_values
		if(APIHelper::apiVersionIsBelow(2)){
			return $this->writeJsonWithoutGlobalFields(200, $sources);
		} else{
			return $this->writeJsonWithGlobalFields(200, new ConnectorListResponse($sources));
		}
	}
	/**
	 * @return \App\DataSources\QMDataSource[]
	 */
	public static function getDataSources(): array{
		try {
			if($user = QMAuth::getQMUser()){
				$sources = $user->getDataSources();
				foreach($sources as $source){
					if($source instanceof QMConnector){
						$source->addExtendPropertiesForRequest();
					}
				}
				return $sources;
			}
		} catch (UnauthorizedException $e) {
			// This is fine, we just don't have a user
		}
		return QMConnector::getUnauthenticated();
	}
}
