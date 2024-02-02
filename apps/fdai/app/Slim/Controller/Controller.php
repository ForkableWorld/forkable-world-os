<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Exceptions\AccessTokenExpiredException;
use App\Exceptions\BadRequestException;
use App\Exceptions\InvalidClientIdException;
use App\Exceptions\QMException;
use App\Exceptions\UnauthorizedException;
use App\Logging\QMLog;
use App\Models\UserVariable;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Study\StudyUserTitleProperty;
use App\Slim\Configuration\RouteConfiguration;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\QMResponseBody;
use App\Slim\Model\Reminders\QMTrackingReminder;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Slim\QMSlim;
use App\Slim\View\Request\QMRequest;
use App\Slim\View\Request\Request;
use App\Storage\Memory;
use App\Traits\HasClassName;
use App\Traits\LoggerTrait;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Utils\APIHelper;
use App\Utils\AppMode;
use App\Utils\UrlHelper;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use SendGrid\Mail\MimeType;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
abstract class Controller {
	use LoggerTrait, HasClassName;
	/**
	 * The namespace relevant classes are in.
	 */
	public const NAMESPACE_CONTROLLER = 'App\\Slim\\Controller';
	public const NAMESPACE_REQUEST = 'App\\Slim\\View\\Request';
	/**
	 * @var QMSlim The QM API application
	 */
	private $app;
	/**
	 * @var Request The request object.
	 */
	private $request;
	public $userVariable;
	/**
	 * Construct a new controller.
	 */
	public function __construct(){
		if(!getenv('APP_URL')){
			return; // This is to prevent issues when Laravel artisan is just scanning routes.
		}
		$url = QMRequest::current();
		if(!isset($_SERVER['REQUEST_URI'])){
			$_SERVER['REQUEST_URI'] = $url;
		} // for testing
		//SolutionButton::add("Last " . qm_request()->getMethod() . " Request", $url);
		AppMode::setIsApiRequest(true);
		Memory::set(Memory::SLIM, true);
		$this->app = QMSlim::getInstance();
	}
	/**
	 * @return \Slim\Http\Request
	 */
	public function request(): \Slim\Http\Request{
		return $this->getApp()->request();
	}
    /**
     * @param null $key
     * @param null $default
     * @return array|string
     */
	public function params($key = null, $default = null){
        $QMSlim = $this->getApp();
        if($QMSlim){
            return $QMSlim->params($key, $default);
        }
        return request()->input($key, $default);
	}
	/**
	 * @return QMSlim The QM API application
	 */
	public function getApp(): ?QMSlim{
		if(!$this->app){
			$this->app = QMSlim::getInstance();
		}
		if(!$this->app){
			$this->app = new QMSlim(AppMode::isConsole());
		}
		return $this->app;
	}
	/**
	 * @param int $maxAgeSeconds
	 * @return void
	 */
	public function setCacheControlHeader(int $maxAgeSeconds = 5){
        $app = $this->getApp();
        if($app){
            $app->setCacheControlHeader($maxAgeSeconds);
        } else {
            header('Cache-Control: max-age=' . $maxAgeSeconds . ', must-revalidate');
        }
	}
    /**
     * @param int $responseStatusCode
     * @param array|object $responseBody
     * @param int|null $jsonEncodeOption
     * @return Response
     */
	public function writeJsonWithGlobalFields(int $responseStatusCode, $responseBody, int $jsonEncodeOption = null): JsonResponse
    {
        if(!QMSlim::getInstance()){
            return new JsonResponse($responseBody, $responseStatusCode);
        }
		$app = $this->getApp();
		return $app->writeJsonWithGlobalFields($responseStatusCode, $responseBody, $jsonEncodeOption);
	}

    /**
     * @param int $responseStatusCode
     * @param mixed $responseDataArray
     * @param int|null $jsonEncodeOption
     * @return JsonResponse
     */
	public function writeJsonWithoutGlobalFields(int $responseStatusCode, $responseDataArray,
		int $jsonEncodeOption = null, array $headers = []): JsonResponse{
		if(!$jsonEncodeOption){
			$jsonEncodeOption = JSON_ERROR_UTF8;
		}
        if(!QMSlim::getInstance()){
	        $response = response();
	        return $response->json($responseDataArray, $responseStatusCode, $headers, $jsonEncodeOption);
        }
		return $this->getApp()->writeJsonWithoutGlobalFields($responseStatusCode, $responseDataArray, 
		                                                     $jsonEncodeOption);
	}
	/**
	 * @param int $code
	 * @param string $json
	 * @param $body
	 */
	protected function writeJson(int $code, string $json, $body): void{
		$this->getApp()->writeJson($code, $json, $body);
	}
	/**
	 * @param string $url
	 * @param int $status
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function redirect(string $url, int $status = QMResponseBody::CODE_TEMPORARY_REDIRECT){
		return UrlHelper::redirect($url, $status);
	}
	/**
	 * Get the class name and the relevant part of the namespace of a given controller (e.g.
	 * Variable\ListVariableController).
	 * @param Controller $controller The controller to get the name of.
	 * @return string The name of the controller that was supplied.
	 */
	protected function getControllerClassName(Controller $controller): string{
		return str_replace(self::NAMESPACE_CONTROLLER, '', get_class($controller));
	}
	/**
	 * Get the populated request object for this controller.
	 * @param string $requestClass
	 * @return Request The request object.
	 */
	protected function getRequest(string $requestClass = ''): Request{
		if(!isset($this->request)){
			if(empty($requestClass)){
				// Figure out the name of the controller that was called.
				$controllerClassName = $this->getControllerClassName($this);
				// Figure out the class of the request object.
				$requestClassName = str_replace('Controller', 'Request', $controllerClassName);
				$requestClass = self::NAMESPACE_REQUEST . $requestClassName;
			}
			/**
			 * Instantiate the Request object and populate its properties
			 * @var Request $request
			 */
			$this->request = new $requestClass();
			$app = $this->getApp();
			$this->request->populate($app);
		}
		return $this->request;
	}

    /**
     * @return array
     */
    public static function getRequestParams(): array{
        $body = static::getBodyAsArrayAndReplaceLegacyKeys(true);
        $requestParams = \request()->all();
        if($body){
            $requestParams = array_merge($body, $requestParams);
        }
        return QMStr::properlyFormatRequestParams($requestParams);
    }
	/**
	 * @param string|null $fallback
	 * @return string
	 * @throws UnauthorizedException
	 */
	protected function getClientId(string $fallback = null): string{
		$clientId = BaseClientIdProperty::fromRequest();
		if(!$clientId){
			$clientId = $fallback;
		}
		if(!$clientId){
			throw new InvalidClientIdException($clientId);
		}
		return $clientId;
	}
	/**
	 * @return string
	 */
	public function getNameParam(): ?string{
		return $this->getApp()->request()->get('name');
	}
	/**
	 * @return string
	 */
	public function getUpcParam(){
		$barcodeScannerFormats = [
			"QR_CODE",
			"DATA_MATRIX",
			//"UPC_E", // False positives on Android
			"UPC_A",
			"EAN_8",
			//"EAN_13", // False positives on Android
			"CODE_128",
			"CODE_39",
			"ITF",
		];
		$barcode = false;
		$request = $this->getApp()->request();
		$barcode = $request->get('upc');
		if($request->get('barcode')){
			$barcode = $request->get('barcode');
		}
		if($this->getNameParam()){
			$name = str_replace("%", "", $this->getNameParam());
			if(ctype_digit($name)){
				$barcode = $name;
			}
			foreach($barcodeScannerFormats as $barcodeScannerFormat){
				if(str_contains($name, $barcodeScannerFormat)){
					$barcode = $name;
				}
			}
		}
		if($barcode){
			foreach($barcodeScannerFormats as $barcodeScannerFormat){
				$barcode = str_replace("$barcodeScannerFormat ", "", $barcode);
			}
		}
		return $barcode;
	}
	/**
	 * @return int
	 */
	public function getUserIdParam(): ?int{
		return (int)$this->getApp()->request()->get('userId');
	}
	/**
	 * @return bool
	 * @throws AccessTokenExpiredException
	 */
	public function isAdmin(): bool{
		return QMAuth::isLoggedInAdmin();
	}

    /**
     * @param bool $throwException
     * @return int
     * @throws UnauthorizedException
     */
    public static function getUserIdParamOrAuthenticatedUserId(bool $throwException = false): ?int{
        if(Controller::getRequestParam('userId')){
            return static::getRequestParam('userId');
        }
        if($u = QMAuth::getUser()){
            return $u->getId();
        }
        if($throwException){
            throw new UnauthorizedException("Please log in");
        }
        return null;
    }
	/**
	 * @param UserVariable[]|QMVariable[] $userVariables This avoids swagger errors when it gets null instead of an
     * array
	 * @return UserVariable[]
	 */
	public function unsetNullTagProperties(array $userVariables): array{
		$userVariables = array_values($userVariables);
		$v = $userVariables[0] ?? null;
		if(!$v){
			return [];
		}
		$tagProperties = [];
		foreach($v as $key => $value){
			if(str_contains($key, 'Start')){
				continue; // Don't remove start times
			}
			if(str_contains($key, 'TaggedMeasurement')){
				$tagProperties[] = $key;
			}
			if(str_contains(strtolower($key), 'tag')){
				$tagProperties[] = $key;
			}
		}
		foreach($userVariables as $object){
			foreach($object as $key => $value){
				if(!in_array($key, $tagProperties)){
					continue;
				}
				if($value === null){
					unset($object->$key);
				}
			}
		}
		return array_values($userVariables);
	}

    /**
     * @param $parameterName
     * @param null $default
     * @return array|mixed|null|string
     */
    public static function getRequestParam($parameterName, $default = null){
        $params = request()->all();
        $value = QMArr::getValueForSnakeOrCamelCaseKey($params, $parameterName);
        if($value !== null){
            return $value;
        }
        return $default;
    }
	/**
	 * @param $parameterName
	 * @param null $default
	 * @return array|mixed|null
	 */
	public function getBodyOrQueryParam(string $parameterName, $default = null){
		if(Controller::getRequestParam($parameterName)){
			return static::getRequestParam($parameterName);
		}
		if($this->getBodyParam($parameterName)){
			return $this->getBodyParam($parameterName);
		}
		return $default;
	}
	/**
	 * @param $parameterName
	 * @param null $default
	 * @return int|null
	 */
	public function getParamInt($parameterName, $default = null){
		$param = $this->getApp()->request()->get($parameterName, $default);
		if(!$param){
			return null;
		}
		return (int)$param;
	}
	/**
	 * @param $parameterName
	 * @param null $default
	 * @return bool
	 */
	public function getParamBool($parameterName, $default = null){
		return (bool)$this->getApp()->request()->get($parameterName, $default);
	}
	/**
	 * @throws UnauthorizedException
	 */
	public function makeSureClientCreatedUser(): void{
		$user = QMAuth::getQMUserIfSet();
		if($user->clientId !== $this->getClientId()){
			throw new UnauthorizedException("Not authorized. User was created by " . $user->clientId .
				" and your client id is " . $this->getClientId());
		}
	}
	/**
	 * @param $name
	 * @param bool $required
	 * @param null $legacyKeys
	 * @param null $default
	 * @return mixed|null
	 */
	public function getBodyParam($name, bool $required = false, $legacyKeys = null, $default = null){
		$body = static::getBodyAsArrayAndReplaceLegacyKeys(true, $legacyKeys);
		$value = QMArr::getValueForSnakeOrCamelCaseKey($body, $name);
		if($value !== null){
			return $value;
		}
		if($required){
			throw new BadRequestHttpException("Please provide $name in body of request");
		}
		return $default;
	}

    /**
     * @param bool $includeClientId
     * @param array|null $legacyKeys
     * @return array
     */
    public static function getBodyAsArrayAndReplaceLegacyKeys(bool $includeClientId, array $legacyKeys = null): array{
        $body = QMRequest::body();
        if($legacyKeys){
            $body = QMArr::replaceLegacyKeys($body, QMUserVariableRelationship::getLegacyRequestParameters());
        }
		if(!$includeClientId){
			unset($body['clientId']);
			unset($body['client_id']);
		}
        return $body ?? [];
    }
	/**
	 * @return array|object
	 */
	public function getBody(){
        if(!QMSlim::getInstance()){
            return \request()->input();
        }
        $app = $this->getApp();
		if(!$app){
			return [];
		}
		$body = $app->request->getBody();
		$body = QMStr::jsonDecodeIfNecessary($body, true);
		return $body;
	}
	/**
	 * @return bool
	 */
	public function clientAcceptsHtml(): bool{
		return isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'html');
	}
	/**
	 * @return string|null
	 */
	public function getEmail(): ?string{
		$email = $this->getBodyOrQueryParam('email');
		if(!$email){
			return null;
		}
		if(filter_var($email, FILTER_VALIDATE_EMAIL)){
			QMLog::debug("$email is a valid email address");
		} else{
			throw new QMException(400, "$email is not a valid email address");
		}
		return $email;
	}
	/**
	 * @return string|null
	 */
	public function getClientEmail(): ?string{
		$email = QMRequest::getParam([
			'physician_email',
			'client_email',
			'email',
			'email_address',
		], null, false);
		if(!$email){
			return null;
		}
		if(filter_var($email, FILTER_VALIDATE_EMAIL)){
			QMLog::debug("$email is a valid email address");
		} else{
			throw new QMException(400, "$email is not a valid email address");
		}
		return $email;
	}
	/**
	 * @param bool $throwException
	 * @return array|mixed|null|string
	 */
	public function getStudyTitle(bool $throwException = true){
		$result = StudyUserTitleProperty::fromRequest();
		if(!$result && $throwException){
			throw new BadRequestHttpException("Please provide study_title parameter");
		}
		return $result;
	}
	/**
	 * @return bool
	 */
	protected function noRedirect(): bool{
		return QMRequest::getParam('noRedirect') || !$this->clientAcceptsHtml();
	}
	/**
	 * @param array|object $response
	 */
	public static function setUserVariableIdToVariableIdIfNecessary($response): void{
		// This is needed to maintain backward compatibility with clients that I can't update
		if(APIHelper::apiVersionIsAbove(4)){
			return;
		}
		if(!is_array($response)){
			return;
		}
		if(isset($response['data'])){
			$response = $response['data'];
		}
		if(!is_array($response)){
			return;
		}
		if(isset($response['userVariable'])){ // Must be done first
			$response['userVariable']->id = $response['userVariable']->variableId;
		}
		if(isset($response['userVariables'])){
			$response = $response['userVariables'];
		}
		if(isset($response['variables'])){
			$response = $response['variables'];
		}
		foreach($response as $variable){
			if($variable instanceof QMTrackingReminderNotification){
				continue;
			}
			if($variable instanceof QMTrackingReminder){
				continue;
			}
			if($variable instanceof QMUserVariable){
				$variable->id = $variable->variableId;
			}
		}
	}
	/**
	 * @param $data
	 * @return mixed
	 */
	public function outputHtmlToBrowser($data){
		$this->getApp()->response->headers->set('Content-Type', MimeType::HTML);
		echo $data;
		return $data;
	}
	/**
	 * @return string
	 */
	public function __toString(){
		return $this->getMethod() . " " . QMRequest::current() . " " . (new \ReflectionClass(static::class))->getShortName();
	}
	private function getMethod(): string{
		return qm_request()->getMethod();
	}
	public static function getUrl(array $params = []):string{
		$url = \App\Utils\Env::getAppUrl().RouteConfiguration::getPathByControllerClass(static::class);
		$url = UrlHelper::addParams($url, $params);
		return $url;
	}
	/**
	 * @param bool $includeClientId
	 * @param bool $throwException
	 * @return array
	 */
	protected function getRequestJsonBodyAsArray(bool $includeClientId, bool $throwException = true): array{
		$decoded = QMRequest::body();
		if(!$decoded){
			if($throwException){throw new BadRequestException(QMSlim::ERROR_EMPTY_REQUEST);}
			return [];
		}
		BaseClientIdProperty::setInMemory($decoded);
		if(!$includeClientId){unset($decoded['clientId'], $decoded['client_id']);}
		return $decoded;
	}
}
