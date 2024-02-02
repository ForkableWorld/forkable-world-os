<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Pair;
use App\Exceptions\BadRequestException;
use App\Slim\Controller\GetController;
use App\Slim\View\Request\Pair\GetPairRequest;
use App\Types\QMArr;
use App\Utils\APIHelper;
/** Class Pairs
 * @package App\Slim\Controller * @SWG\Resource(
 *     apiVersion="1.0",
 *     swaggerVersion="1.2",
 *     resourcePath="/pairs",
 *     description="Returns pairs of the predictor variable and effect measurements to be used for analysis.",
 *     produces="['application/json']"
 * )
 * @SWG\Model(
 *     id="Pairs",
 *     @SWG\Property(
 *         name="name", type="string", required=true, description="Category name"
 *     ),
 * )
 */
class GetPairController extends GetController {
	/**
	 * @param array $params
	 * @return array
	 */
	protected function handlePairsRequest(array $params): array{
		$causeName = QMArr::getValue($params, ['cause', 'causeVariableName']);
		if(!$causeName){throw new BadRequestException('Please provide causeVariableName');}
		$effectName = QMArr::getValue($params, ['effect', 'effectVariableName']);
		if(!$effectName){throw new BadRequestException('Please provide effectVariableName');}
		$req = new GetPairRequest($causeName, $effectName);
		$req->populate($this->getApp());
		$allPairs = $req->createPairsFromDailyMeasurementsWithTagsAndFilling();
		if(isset($params['format']) && $params['format'] === 'csv'){
			$req->toCsv();
		}
		if(APIHelper::isApiVersion(3)){return ['allPairs' => $allPairs];}
		return $allPairs;
	}
	public function get(){
		$params = $this->getApp()->request->params();
		$response = self::handlePairsRequest($params);
		if(APIHelper::apiVersionIsAbove(3)){
			return $this->writeJsonWithGlobalFields(200, $response);
		} elseif(APIHelper::isApiVersion(3)){
			$this->writeJsonWithoutGlobalFields(200, $response['allPairs']);
		} else{
			$this->writeJsonWithoutGlobalFields(200, $response);
		}
	}
}
