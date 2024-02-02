<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Utils;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NoGeoDataException;
use App\Exceptions\RateLimitConnectorException;
use App\Logging\QMLog;
use App\Models\IpDatum;
use App\Properties\Base\BaseCountryProperty;
use App\Traits\LoggerTrait;
use App\Types\QMStr;

class GeoLocation {
	use LoggerTrait;
	public static $latLongFromZip;
	public static $locations;
    private static array $results = [];
    public $callingCode;
	public $city;
	public $connectionType;
	public $continentCode;
	public $continentName;
	public $countryName;
	public $currency;
	public $id;
	public $ip;
	public $isp;
	public $languages;
	public $organization;
	public $stateProv;
	public $timeZone;
	public ?string $zipcode;
	public float $latitude;
	public $location;
	public float $longitude;
	public $regionCode;
	public $regionName;
	public $type;
	public $zip;
	public $countryCode;
	/**
	 * @param null $obj
	 */
	public function __construct($obj = null){
		if($obj){
			foreach($obj as $Key => $value){
				$this->$Key = $value;
			}
			$this->getZipcode();
		}
	}
	/**
	 * @param null $ip
	 * @param string $purpose
	 * @param bool $deep_detect
	 * @return array|null|string
	 */
	public static function ip_info($ip = null, string $purpose = "location", bool $deep_detect = true){
		$output = null;
		if(filter_var($ip, FILTER_VALIDATE_IP) === false){
			$ip = $_SERVER["REMOTE_ADDR"] ?? null;
			if($deep_detect){
				if(filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)){
					$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
				}
				if(filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)){
					$ip = $_SERVER['HTTP_CLIENT_IP'];
				}
			}
		}
		$purpose = str_replace([
			"name",
			"\n",
			"\t",
			" ",
			"-",
			"_",
		], null, strtolower(trim($purpose)));
		$support = [
			"country",
			"countrycode",
			"state",
			"region",
			"city",
			"location",
			"address",
		];
		$continents = [
			"AF" => "Africa",
			"AN" => "Antarctica",
			"AS" => "Asia",
			"EU" => "Europe",
			"OC" => "Australia (Oceania)",
			"NA" => "North America",
			"SA" => "South America",
		];
		if(filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support, true)){
			$ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
			if(@strlen(trim($ipdat->geoplugin_countryCode)) == 2){
				switch($purpose) {
					case "location":
						$output = [
							"city" => @$ipdat->geoplugin_city,
							"state" => @$ipdat->geoplugin_regionName,
							"country" => @$ipdat->geoplugin_countryName,
							"country_code" => @$ipdat->geoplugin_countryCode,
							"continent" => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
							"continent_code" => @$ipdat->geoplugin_continentCode,
						];
						break;
					case "address":
						$address = [$ipdat->geoplugin_countryName];
						if(@strlen($ipdat->geoplugin_regionName) >= 1){
							$address[] = $ipdat->geoplugin_regionName;
						}
						if(@strlen($ipdat->geoplugin_city) >= 1){
							$address[] = $ipdat->geoplugin_city;
						}
						$output = implode(", ", array_reverse($address));
						break;
					case "city":
						$output = @$ipdat->geoplugin_city;
						break;
					case "state":
						$output = @$ipdat->geoplugin_stateName;
						break;
					case "region":
						$output = @$ipdat->geoplugin_regionName;
						break;
					case "country":
						$output = @$ipdat->geoplugin_countryName;
						break;
					case "countrycode":
						$output = @$ipdat->geoplugin_countryCode;
						break;
				}
			}
		}
		return $output;
	}
	/**
	 * @param string $ip
	 * @return object
	 * @throws \App\Exceptions\NoGeoDataException
	 */
	private static function getLocation_INCLUDING_LatitudeAndLongitudeFromIpStack(string $ip): object {
		/** @noinspection SpellCheckingInspection */
		$apiKey = \App\Utils\Env::get('IPSTACK_API_KEY');
		if(!$apiKey){
            throw new NoGeoDataException("Please set env 'IPSTACK_API_KEY'");
		}
        QMLog::info("http://api.ipstack.com/$ip...");
		$url = "http://api.ipstack.com/$ip?access_key=" . $apiKey;
		$resultWithLatLong = self::getRequest($url);
		if(isset($resultWithLatLong->success) && !$resultWithLatLong->success){
			throw new NoGeoDataException($resultWithLatLong->error->info);
		}
		return $resultWithLatLong;
	}
	/**
	 * @param $value
	 * @return null
	 */
	public static function returnNullIfUnknown($value){
		return ($value === "Unknown") ? null : $value;
	}
	/**
	 * @param array $params
	 * @return mixed
	 * @throws NoGeoDataException
	 * @throws RateLimitConnectorException
	 */
	public static function googleMapsRequest(array $params){
		//Send request and receive json data by latitude longitude
		$url =
			'https://maps.googleapis.com/maps/api/geocode/json?sensor=true_or_false&key=' . getenv('GOOGLE_MAPS_API_KEY');
		$url = UrlHelper::addParams($url, $params);
		$obj = APIHelper::getRequest($url);
		if($obj->status === "OK"){
			return $obj->results[0];
		}
		throw new NoGeoDataException("$url request failed because: " . QMLog::print_r($obj));
	}
	/**
	 * @param string $ip
	 * @return string
	 */
	public static function getCity(string $ip = "Visitor"){
		$result = self::ip_info($ip, "City");
		return $result;
	}
	/**
	 * @param array|object $arrayOrObject
	 * @return \App\Utils\GeoLocation|array|object
	 */
	public static function instantiateIfNecessary(array|object|string $arrayOrObject){
		if(!$arrayOrObject){
			return $arrayOrObject;
		}
		if($arrayOrObject instanceof static){
			return $arrayOrObject;
		}
		$model = new static($arrayOrObject);
		return $model;
	}
	/**
	 * @param string $ip
	 * @param string $lang
	 * @param string $fields
	 * @param string $excludes
	 * @return IpDatum
	 * @throws \App\Exceptions\NoGeoDataException
	 */
	public static function ipData(string $ip, string $lang = "en", string $fields = "*",
		string $excludes = ""): IpDatum {

		if(isset(self::$locations[$ip])){
			if(self::$locations[$ip] instanceof NoGeoDataException){
				throw self::$locations[$ip];
			}
			return self::$locations[$ip];
		}
		$ipData = IpDatum::whereIp($ip)->first();
		if($ipData){
			return self::$locations[$ip] = $ipData;
		}
		try {
			$response = self::getLocation_INCLUDING_LatitudeAndLongitudeFromIpStack($ip);
		} catch (NoGeoDataException $e) {
			try {
				$response = self::getLocation_WITHOUT_LatitudeAndLongitudeFromIpGeolocation($ip, $lang, $fields, $excludes);
			} catch (NoGeoDataException $e) {
				self::$locations[$ip] = $e;
				throw $e;
			}
		}
        if(!isset($response->country_code) && isset($response->country_code2)){
            $response->country_code = $response->country_code2;
        }
        if(!isset($response->country_code)){
            $e = new NoGeoDataException("No good geo data for $ip!  Got: ".\App\Logging\QMLog::print_r($response, true));
            self::$locations[$ip] = $e;
            throw $e;
        }
		$ipData = new IpDatum();
		$ipData->populate((array)$response);
		try {
			$ipData->save();
		} catch (ModelValidationException $e) {
			le($e);
		}
		return self::$locations[$ip] = $ipData;
	}

    /**
     * @param string $url
     * @return bool|mixed|string
     * @throws NoGeoDataException
     */
	public static function getRequest(string $url){
        if(isset(static::$results[$url])){
            return static::$results[$url];
        }
		$cURL = curl_init();
		curl_setopt($cURL, CURLOPT_URL, $url);
		curl_setopt($cURL, CURLOPT_HTTPGET, true);
		curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cURL, CURLOPT_TIMEOUT, 5); //timeout in seconds
		curl_setopt($cURL, CURLOPT_HTTPHEADER, [
				'Content-Type: application/json',
				'Accept: application/json',
			]);
		$result = curl_exec($cURL);
        static::$results[$url] = $result = json_decode($result, false);
        if(!$result){
            if (curl_errno($cURL)) {
                $error_msg = curl_error($cURL);
            }
            curl_close($cURL);
            if (isset($error_msg)) {
                throw new NoGeoDataException(__METHOD__.": ". $error_msg);
            } else {
                throw new NoGeoDataException(__METHOD__.": No response from $url");
            }
        }
		return static::$results[$url] = $result;
	}
	/**
	 * @param string $ip
	 * @param string $lang
	 * @param string $fields
	 * @param string $excludes
	 * @return object
	 * @throws \App\Exceptions\NoGeoDataException
	 */
	private static function getLocation_WITHOUT_LatitudeAndLongitudeFromIpGeolocation(string $ip, string $lang,
		string $fields, string $excludes): object {
		// https://app.ipgeolocation.io/
		// m@thinkbynumbers.org
		// pw: 0X6ErYKA&CTN
		$apiKey = \App\Utils\Env::get('IP_GEOLOCATION_IO_API_KEY');
        if(!$apiKey){
            throw new NoGeoDataException("Please set env 'IP_GEOLOCATION_IO_API_KEY'");
        }
		$url = "https://api.ipgeolocation.io/ipgeo?apiKey=" . $apiKey . "&ip=" . $ip . "&lang=" . $lang . "&fields=" .
			$fields . "&excludes=" . $excludes;
		$resultWithoutLatLong = self::getRequest($url);
		if(isset($resultWithoutLatLong->message)){
			throw new NoGeoDataException("Could not get $ip location from api.ipgeolocation.io because $resultWithoutLatLong->message");
		}
		return $resultWithoutLatLong;
	}
	/**
	 * @param array $array
	 * @return string
	 */
	public static function getLocationFromArrayOrRequest(array $array = []): ?string{
		if(isset($array['location'])){
			$_SERVER['HTTP_LOCATION'] = $array['location'];
		}
		if(!isset($_SERVER['HTTP_LOCATION'])){
			return IPHelper::getClientIp();
		}
		return self::returnNullIfUnknown($_SERVER['HTTP_LOCATION']);
	}
	/**
	 * @return string
	 */
	public function getZipcode(): ?string{
		$zip = $this->zipcode ?? $this->zip;
		if($zip){
			$this->zipcode = $this->zip = $zip;
		}
		if(!$this->zipcode){
			return null;
		}
		if(!BaseCountryProperty::getCountryCodeFromZip($zip)){
			$this->logError("$zip is not a valid postal code for any available country!");
		}
		return $zip;
	}
	/**
	 * @return float
	 */
	public function getLongitude(): float{
		return $this->longitude;
	}
	/**
	 * @return float
	 */
	public function getLatitude(): float{
		return $this->latitude;
	}
	/**
	 * @return bool|string
	 */
	public function __toString(){
		return QMStr::printStringsAndNumbers($this);
	}
}
