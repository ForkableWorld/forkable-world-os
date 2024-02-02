<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Pair;
use App\Slim\Controller\GetController;
use App\Slim\View\Request\Pair\GetPairRequest;
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
class GetPairCsvController extends GetPairController {
	public function get(){
		$this->setCacheControlHeader(5 * 60);
		/** @var GetPairRequest $request */
		$requestParams = $this->getApp()->request->params();
		$requestParams['limit'] = 0;
		$requestParams['format'] = 'csv';
		$this->handlePairsRequest($requestParams);
	}
}
