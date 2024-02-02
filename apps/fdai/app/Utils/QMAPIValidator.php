<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Utils;
use App\Exceptions\BadRequestException;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\View\Request\QMRequest;
use App\Types\QMStr;
class QMAPIValidator {
	public static $GLOBAL_PARAMS = [
		'refresh',
		'user',
		'limit',
		'offset',
		'reason',
		'sha',
		'sort',
		'log',
		'pwd',
		'token',
		'createdAt',
		'updatedAt',
		'access_token',
		'accessToken',
		'XDEBUG_SESSION_START',
		'xDEBUGSESSIONSTART',
		'id',
		'appName',
		'appVersion',
		'clientId',
		'userId',
		'organizationToken',
		'deletedAt',
		'platformType',
		'useWritableConnection',
		'useReadOnlyConnection',
		'format',
		'platform',
		QMRequest::PARAM_PROFILE,
	];
	/**
	 * @param $allowedParams
	 * @param $params
	 * @param $methodName
	 * @throws BadRequestException
	 */
	public static function validateParams($allowedParams, $params, $methodName){
		if(!$params){
			return;
		}
		$mergedAllowedParams = array_merge($allowedParams, self::$GLOBAL_PARAMS);
		foreach($params as $singleParam){
			if(!in_array($singleParam, $mergedAllowedParams, true)){
				$allowedParamsString = implode(", ", $mergedAllowedParams);
				throw new BadRequestException('Supplied parameter ' . $singleParam .
					' does not exist. Allowed parameters are: ' . $allowedParamsString . ".  See " .
					'<a href="https://docs.quantimo.do">API docs</a> for more info. ' .
                    QMStr::CONTACT_MIKE_FOR_HELP_STRING, [
					$singleParam,
					QMRequest::host(),
					$methodName,
				]);
            }
		}
	}
	/**
	 * @param [] $requestParams
	 * @return array
	 */
	public static function convertToIntegerIfNecessary($params): array{
		$integerFieldNames = [
			'limit',
			'offset',
			'id',
		];
		foreach($params as $name => $value){
			if(in_array($name, $integerFieldNames, true) && isset($value) && is_numeric($value)){
				$params[$name] = (int)$value;
			}
		}
		foreach($params as $name => $value){
			if(strpos($name, 'Id') !== false && stripos($name, 'clientId') === false && is_numeric($value)){
				$params[$name] = (int)$value;
			}
		}
		foreach($params as $name => $value){
			$params[$name] = self::convertStingTimeStampToInteger($value, $name);
		}
		return $params;
	}
	/**
	 * @param mixed $value
	 * @param string $key
	 * @return mixed
	 */
	public static function convertStingTimeStampToInteger($value, string $key){
		if(self::isValidTimeStamp($value, $key)){
			return (int)$value;
		}
		return $value;
	}
	/**
	 * @param $value
	 * @param string $key
	 * @return bool
	 */
	public static function isValidTimeStamp($value, string $key): bool{
		$timestampProperties = [
			'earliestTaggedMeasurementTime',
			'latestTaggedMeasurementTime',
			'startTime',
		];
		if(!in_array($key, $timestampProperties, true)){
			return false;
		}
		return ((string)(int)$value === $value) && ($value <= PHP_INT_MAX) && ($value >= ~PHP_INT_MAX);
	}
	/**
	 * @param mixed $string
	 * @return bool
	 */
	public static function checkBool($string): bool{
		if(!is_string($string)){
			return false;
		}
		$string = strtolower($string);
		return in_array($string, [
			"true",
			"false",
			"1",
			"0",
			QMMeasurement::STRING_YES,
			QMMeasurement::STRING_NO,
		], true);
	}
	/**
	 * @param [] $requestParams
	 * @return array
	 */
	public static function convertToBooleanIfNecessary($array): array{
		$notBool = [
			'limit',
			'offset',
			'userId',
			'startTime',
			'startTimeEpoch',
			'value',
			'endTime',
			'clientId',
			'id',
		];
		foreach($array as $parameterName => $parameterValue){
			if(!in_array($parameterName, $notBool, true) && self::checkBool($parameterValue)){
				$array[$parameterName] = self::convertToBoolean($parameterValue);
			}
		}
		return $array;
	}
	/**
	 * @param $value
	 * @return mixed
	 */
	public static function convertToBoolean($value){
		return filter_var((string)$value, FILTER_VALIDATE_BOOLEAN);
	}
}
