<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Http\Controllers;
use App\Charts\QMHighcharts\DBHighstock;
use App\Utils\IonicHelper;
use App\Utils\UserAgent;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;
class ChartsController extends Controller {
	public const CHARTS_PATH = "charts";
	public function get(Request $request){
		if($png = $request->get('pngUrl')){
			return $this->getPngOrRedirectToIonicChartsForVariable($request);
		}
		return $this->getDynamicChart($request);
	}
	/**
	 * @param Request $request
	 * @return Factory|RedirectResponse|Redirector|View
	 */
	public function getPngOrRedirectToIonicChartsForVariable(Request $request){
		if(UserAgent::isBot()){
			return redirect($this->getIonicChartUrl());
		} else{
			return $this->chartPngView($request);
		}
	}
	/**
	 * @param Request $request
	 * @return string
	 */
	public function getDynamicChart(Request $request): string{
		$class = $request->get('class');
		/** @var DBHighstock $chart */
		$params = $request->input();
		unset($params['class']);
		$chart = new $class($params);
		return $chart->getMaterialView();
	}
	/**
	 * @return string
	 */
	protected function getIonicChartUrl(): string{
		$ionicUrl = IonicHelper::getChartsUrl($_GET);
		return $ionicUrl;
	}
	/**
	 * @param Request $request
	 * @return Factory|View
	 */
	protected function chartPngView(Request $request){
		$meta = [
			'title' => "Check out my " . $request->get('variableName') . ' data!',
			'description' => 'Please help us to advance citizen science!',
			'image' => $request->get('pngUrl'),
			'imageWidth' => 600,
			'imageHeight' => 600,
			'dynamicUrl' => $this->getIonicChartUrl(),
		];
		return view('chart-png-for-facebook', ['meta' => $meta]);
	}
}
