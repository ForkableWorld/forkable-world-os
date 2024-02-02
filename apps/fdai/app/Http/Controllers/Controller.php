<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;
use App\Logging\QMLog;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Middleware\QMAuth;
use App\Traits\HttpTraits\SavesMeasurements;
use App\Types\QMStr;
use App\Utils\QMRoute;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as IlluminateController;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
// Don't rename Controller because Nice Artisan requires it be called Controller specifically
class Controller extends IlluminateController {
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    use SavesMeasurements;
	public Request $request;
	/**
	 * Controller constructor.
	 * @param Request $request
	 */
	public function __construct(Request $request){
		$this->request = $request;
		$this->middleware('web');
	}
	protected function expectsJson(){
		return $this->request->expectsJson() || $this->request->input('format') === 'json';
	}
	/**
	 * @param string $path
	 * @param array $params
	 * @return RedirectResponse
	 */
	public static function redirectWithAccessToken(string $path, array $params = []): RedirectResponse{
		if($user = QMAuth::getUser()){
			$clientId = BaseClientIdProperty::fromRequest() ?? BaseClientIdProperty::CLIENT_ID_QUANTIMODO;
			$params[BaseAccessTokenProperty::URL_PARAM_NAME] = $user->getOrCreateAccessTokenString($clientId);
		}
		return Redirect::route($path, $params);
	}
	/**
	 * @return Request
	 */
	public function getRequest(): Request{
		if(!isset($this->request)){
			$this->request = request();
		}
		return $this->request;
	}
	/**
	 * @return array
	 */
	public function getUnderscoreInputs(): array{
		$inputs = $this->getRequest()->all();
		$inputs = QMStr::convertKeysToUnderscore($inputs);
		return $inputs;
	}
	/**
	 * @param string $redirectionRoute
	 * @param array $redirectionParameters
	 * @param array $jsonResponseData
	 * @param string $successOrError
	 * @param string $message
	 * @param int $statusCode
	 * @return JsonResponse|RedirectResponse
     * @internal param array $responseBody
	 * @internal param string $key
	 * @internal param string $successOrError
	 */
	protected function getResponse(string $redirectionRoute, array $redirectionParameters, array $jsonResponseData = [],
		string $successOrError = 'success', string $message = '', int $statusCode = 200){
		if(!$this->weShouldReturnJsonResponse()){
			return Redirect::route($redirectionRoute, $redirectionParameters)->with($successOrError, $message);
		}
		$response = [
			'message' => $message,
			'success' => $successOrError === 'success',
			'data' => $jsonResponseData,
		];
		if(!$response['success'] && $response['message']){
			$response['error'] = $response['message'];
		}
		return response()->json($response, $statusCode);
	}
	/**
	 * @return bool
	 */
	protected function weShouldReturnJsonResponse(): bool{
		if($this->getRequest()->acceptsHtml()){
			return false;
		}
		if($this->getRequest()->acceptsJson()){
			return true;
		}
		return $this->getRequest()->ajax();
	}
	/**
	 * @param string $name
	 * @param Request $request
	 * @param array $params
	 * @return Factory|View
	 */
	protected function getViewWithRequestParams(string $name, Request $request, array $params = []){
		// TODO: Figure out why Input::get doesn't work and we need to always pass Request $request
		// $params = array_merge($request->all(), $params);
		$error_message = $request->input('error_message');
		if($error_message){
			$params['error_message'] = urldecode($error_message);
		}
		return view($name, $params);
	}
	/**
	 * @param $data
	 * @return array
	 */
	public function hydrateResponse($data): array{
		$response = [];
		if(is_object($data)){
			$data = json_decode(json_encode($data), true);
		}
		foreach($data as $key => $value){
			if(is_array($value)){
				$response[QMStr::camelize($key)] = $this->hydrateResponse($value);
			} else{
				$response[QMStr::camelize($key)] = $value;
			}
		}
		return $response;
	}
	/**
	 * @return array
	 */
	public static function getRoutes(): array{
		return QMRoute::getRoutes();
	}
    public static function getURL(array $params = []): string
    {
        $r = QMRoute::findByControllerAndAction(static::class);
        return $r->getUrl($params);
    }

    protected function logError(string $string)
    {
        QMLog::error($string);
    }
}
