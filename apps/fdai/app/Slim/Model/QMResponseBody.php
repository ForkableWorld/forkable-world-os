<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model;
use App\CodeGenerators\Swagger\SwaggerJson;
use App\Exceptions\ExceptionHandler;
use App\Logging\QMLog;
use App\Models\OAClient;
use App\Models\User;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Auth\QMAccessToken;
use App\Slim\QMSlim;
use App\Slim\View\Request\QMRequest;
use App\Storage\Memory;
use App\Types\ObjectHelper;
use App\Utils\AppMode;
use App\Utils\EnvOverride;
use Exception;
use Slim\Http\Request;
use Tests\TestGenerators\ApiTestFile;
class QMResponseBody {
	public const STATUS_OK = "OK";
	public const STATUS_ERROR = "ERROR";
	public const CODE_OK = 200;
	public const CODE_ERROR = 400;
	public const CODE_TEMPORARY_REDIRECT = 302;
	public $success = true;
	public $status = self::STATUS_OK;
	public $code = self::CODE_OK;
	public $description;
	public $summary;
	public $errors = [];
	//public $errorMessage;
	public $sessionTokenObject;
	public $avatar;
	public $warnings;
	/**
	 * @var object|array
	 */
	public $data;
	/**
	 * QMResponseBody constructor.
	 * @param null $responseArray
	 * @param int|null $code
	 */
	public function __construct($responseArray = null, int $code = null){
		if($responseArray){
			$this->populate($responseArray);
		}
		if($code){
			$this->code = $code;
			if($code > 399){
				$this->success = false;
				if($this->status === self::STATUS_OK){
					$this->status = self::STATUS_ERROR;
				}
			}
		}
	}
	public function addErrorsDescriptionAndTokens(){
		try {
			$this->addSessionTokenObjectIfNecessary();
		} catch (\Throwable $e) {
			QMLog::error(__METHOD__.": ".$e->getMessage());
		}
		$this->addErrorMessageIfNecessary();
		$this->addPathMethodDescription();
		$this->addPathMethodSummary();
	}
	/**
	 * @return array
	 */
	public function toArray(): array{
		return (array)$this;
	}
	/**
	 * @param $data
	 */
	private function populate($data){
		$values = ObjectHelper::getNonNullValuesWithCamelKeys($data);
		foreach($values as $key => $value){
			$this->$key = $value;
		}
	}
	private function addPathMethodDescription(){
		if(!$this->description || $this->description === true){
			try {
				$this->description = SwaggerJson::getPathMethodDescription($this->getPath(), $this->getMethod());
			} catch (Exception $e) {
				ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
			}
		}
	}
	/**
	 * @return string
	 */
	private function getMethod(): string{
		return strtolower(qm_request()->getMethod());
	}
	/**
	 * @return string
	 */
	private function getPath(): string{
		return QMRequest::getRequestPathWithoutQuery();
	}
	private function addPathMethodSummary(){
		if(!$this->summary){
			try {
				$this->summary = SwaggerJson::getPathMethodSummary($this->getPath(), $this->getMethod());
			} catch (Exception $e) {
				ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
			}
		}
	}
	protected function addErrorMessageIfNecessary(){
		$this->warnings = Memory::getClientWarnings();
		if(isset($this->error) && !$this->errors){
			$this->errors[] = $this->error;
		}
		if(isset($this->error) && !$this->data){
			$this->data = $this->error;
		}
	}
	/**
	 * @param bool $success
	 */
	public function setSuccess(bool $success){
		$this->success = $success;
	}
	/**
	 * @param string $status
	 */
	public function setStatus(string $status){
		$this->status = $status;
	}
	/**
	 * @param string $summary
	 */
	public function setSummary(string $summary){
		$this->summary = $summary;
	}
	protected function addSessionTokenObjectIfNecessary(){
		$user = QMAuth::getQMUserIfSet();  // Don't use getOrAuthenticateUser because we throw a double exception if not authorized
		$clientUserId = QMRequest::getParam('clientUserId');
		$clientId = BaseClientIdProperty::fromRequest(false);
		if(!$user && $clientUserId && $clientId){
			$data = QMRequest::getInput();
			$data = array_merge($data, $_GET);
			$client = OAClient::fromRequest();
			$data[OAClient::FIELD_CLIENT_SECRET] = $client->client_secret;
			$user = User::createUserFromClient($data);
			QMAuth::login($user);
		}
		if($clientUserId && $user && $clientId){
			$this->sessionTokenObject = self::generateSessionTokenObject();
		}
	}
	/**
	 * @return array
	 */
	public static function generateSessionTokenObject(): array{
		$sessionTokenObject = [
			'clientId' => BaseClientIdProperty::fromRequest(true),
			'quantimodoUserId' => QMAuth::id(),
			'sessionToken' => QMAccessToken::createOrGetCsrfSessionToken(),
			'clientUserId' => QMRequest::getParam('clientUserId'),
		];
		return $sessionTokenObject;
	}
	/**
	 * @return mixed
	 */
	public function getErrors(): array{
		return $this->errors;
	}
	/**
	 * @param array $errors
	 */
	public function setErrors(array $errors): void{
		$this->errors = array_values(array_merge($this->errors, $errors));
	}
	/**
	 * @return int
	 */
	public function getCode(): int{
		return $this->code;
	}
	/**
	 * @param int $code
	 */
	public function setCode(int $code): void{
		$this->code = $code;
	}
	/**
	 * @return Request
	 */
	protected function request(): Request{
		return QMSlim::getInstance()->request();
	}
	/**
	 * @param string $errorMessage
	 */
	public function addErrorMessage(string $errorMessage){
		QMLog::error($errorMessage);
		$this->errors[] = $errorMessage;
	}
	/**
	 * @param string $avatar
	 */
	public function setAvatar(string $avatar){
		$this->avatar = $avatar;
	}
}
