<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\View\Request;
use App\Slim\QMSlim;
use App\Traits\LoggerTrait;
use App\Types\QMStr;
use App\Utils\AppMode;
/** Class Request
 * TODO: What should I do if I want to get required numeric parameter? ;)
 * I plan to get some better validation logic for parameters here.
 * @package App\Slim\View\Request
 */
abstract class Request {
	use LoggerTrait;
	/**
	 * @var QMSlim
	 */
	private $app;
	protected $qb;
	/**
	 * Populate this request's properties from an Application instance.
	 * @param QMSlim $app The Application to parse parameters out of.
	 */
	abstract public function populate(QMSlim $app);
	/**
	 * @param QMSlim $app
	 */
	protected function setApplication(QMSlim $app){
		$this->app = $app;
	}
	/**
	 * @return QMSlim
	 */
	public function getApplication(): ?QMSlim{
		return $this->app ?: QMSlim::getInstance();
	}
	/**
	 * @param string $name
	 * @param mixed $default
	 * @return string|int|float|null
	 */
	protected function getParam(string $name, $default = null){
		if(!$this->getApplication() || !AppMode::isApiRequest()){
			return $default;
			//throw new \LogicException("app not set!");
		}
		if(!$this->getApplication()->request){
			le("Request not set!");
		}
		return $this->getApplication()->request->get($name, $default);
	}
	/**
	 * @param array $possibleParameterNames
	 * @return string
	 */
	protected function getParamInArray(array $possibleParameterNames){
		foreach($possibleParameterNames as $possibleParameterName){
			if($this->getParam($possibleParameterName)){
				return $this->getParam($possibleParameterName);
			}
		}
		return null;
	}
	/**
	 * @param string|null $key
	 * @param mixed $default
	 * @return string|array
	 */
	public function params(string $key = null, $default = null){
		$params = $this->getApplication()->params($key, $default);
		return $params;
	}
	/**
	 * @param string $name
	 * @param string $errorMessage
	 * @return string
	 */
	protected function getParamRequired(string $name, string $errorMessage){
		$value = $this->getParam($name, null);
		if($value == null){
			$this->app->haltJson(404, [
				'status' => 400,
				'success' => false,
				'error' => $errorMessage,
			]);
		}
		return $value;
	}
	/**
	 * @param string $name
	 * @param int|null $default
	 * @param string|null $errorMessage
	 * @return int|float
	 */
	protected function getParamNumeric(string $name, int $default = null, string $errorMessage = null){
		$value = $this->getParam($name, $default);
		if($value != null && !is_numeric($value)){
			if(!$errorMessage){
				$errorMessage = "$name must be numeric";
			}
			$code = 400;
			$this->app->haltJson($code, [
				'status' => $code,
				'success' => false,
				'error' => $errorMessage,
			]);
		}
		if($value !== null){
			return (int)$value;
		}
		return null;
	}
	/**
	 * @param $array
	 * @param array $legacyRequestParameterMap
	 * @param array $arrayHolder
	 * @return array
	 */
	public static function properlyFormatRequestParams($array, array $legacyRequestParameterMap = [],
		array $arrayHolder = []): array{
		if(isset($array[0])){
			le("Request params should not have numeric keys!");
		}
		return QMStr::properlyFormatRequestParams($array, $legacyRequestParameterMap, $arrayHolder);
	}
	/**
	 * @return array
	 * Needed for serialization because it can't handle PDO
	 */
	public function __sleep(){
		$serializable = [];
		$this->qb = null;
		foreach($this as $paramName => $paramValue){
			if(!is_string($paramValue) && !is_array($paramValue) && is_callable($paramValue)){
				continue;
			}
			if($paramName === 'qb'){
				continue;
			}
			$serializable[] = $paramName;
		}
		return $serializable;
	}
	/**
	 * @return string
	 */
	public function __toString(){
		return static::class . " __toString result";
	}
}
