<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Computers;
use App\Logging\QMLog;
use App\Types\QMStr;
use App\Utils\Env;
use Aws\Lightsail\LightsailClient;
/**
 * @package App\Computers
 */
class QMLightsailClient extends LightsailClient {
	/**
	 * @return QMLightsailClient
	 */
	public static function client(): LightsailClient{
		return new LightsailClient([
			//'profile' => 'default',  Should we be using this? https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_credentials_profiles.html
			'version' => 'latest',
			'region' => LightsailInstanceResponse::REGION_NAME_US_EAST_1,
			'key' => Env::STORAGE_ACCESS_KEY_ID(),
			'secret' => Env::STORAGE_SECRET_ACCESS_KEY(),
		]);
	}
	/**
	 * Dynamically retrieve attributes on the model.
	 * @param string $key
	 * @return mixed
	 */
	public function __get(string $key){
		return self::client()->$key;
	}
	/**
	 * Dynamically set attributes on the model.
	 * @param string $key
	 * @param mixed  $value
	 * @return void
	 */
	public function __set(string $key, $value){
		return self::client()->$key = $value;
	}
	/**
	 * Handle dynamic static method calls into the method.
	 * @param  string  $method
	 * @param array $parameters
	 * @return mixed
	 */
	public static function __callStatic(string $method, array $parameters){
		return self::client()->$method(...$parameters);
	}
	public static function all(string $what, string $class): array {
		$func = "get$what";
		$client = QMLightsailClient::client();
		QMLog::info(__METHOD__.": $func");
		$res = $client->$func();
		$camel = QMStr::camelize($what);
		$data =  $res->toArray();
		$arr = $data[$camel];
		while($t = $res->get("nextPageToken")){
			$res = $client->$func(["pageToken" => $t]);
			$data = $res->toArray();
			$arr = array_merge($arr, $data[$camel]);
		}
		$models = [];
		//CodeGenerator::generateStaticModelFromResponse("App\\DevOps\\Servers\\LightsailInstance", $arr[0]);
		foreach($arr as $value){
			$m = new $class($value);
			$models[$m->getNameAttribute()] = $m;
		}
		return $models;
	}
}
