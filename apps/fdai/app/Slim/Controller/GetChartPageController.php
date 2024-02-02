<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller;
use App\Charts\DistributionColumnChart;
use App\Charts\MonthlyColumnChart;
use App\Charts\WeekdayColumnChart;
use App\Slim\Middleware\QMAuth;
use App\Slim\QMSlim;
use App\Slim\View\Request\Measurement\GetMeasurementRequest;
use App\Variables\QMUserVariable;
use Exception;
class GetChartPageController extends Controller {
	/**
	 * @param $chartType
	 * @throws Exception
	 */
	public function map($chartType){
		$this->setCacheControlHeader(5 * 60);
		$app = QMSlim::getInstance();
		$user = QMAuth::getUserOrSendToLogin();
		$requestParams = request()->all();
		if(!isset($requestParams['limit'])){
			$requestParams['limit'] = 0;
		}
		$userVariable = QMUserVariable::getByNameOrId($user->id, $requestParams['variableName']);
		$req = new GetMeasurementRequest($requestParams);
		$req->setQmUserVariable($userVariable);
		switch($chartType) {
			case 'distribution':
				$qmChart = new DistributionColumnChart($userVariable);
				break;
			case 'weekday':
				$qmChart = new WeekdayColumnChart($userVariable);
				break;
			case 'monthly':
				$qmChart = new MonthlyColumnChart($userVariable);
				break;
			default:
				throw new Exception('Incorrect connection method');
		}
		$app->response->headers->set('Content-Type',
			'text/html'); // We'll be responding with a form, so set the content type to text/html
		$app->render('HighchartPageTemplate.php', [
			'title' => $qmChart->getTitleAttribute(),
			'highchart' => $qmChart->getHighchartConfig(),
		]);
	}
}
