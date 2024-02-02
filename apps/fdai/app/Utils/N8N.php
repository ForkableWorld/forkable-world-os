<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection HttpUrlsUsage */
namespace App\Utils;
use App\DataSources\LusitanianGuzzleClient;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
class N8N {
	const WEBHOOK_BASE = 'http://quantimodo2.asuscomm.com:5678/webhook';
	private static array $alreadyCalled = [];
	/**
	 * @param string $url
	 * @return array
	 */
	public static function openUrl(string $url): ?array{
		ConsoleLog::debug(__METHOD__);
		if(in_array($url, self::$alreadyCalled)){return null;}
		self::$alreadyCalled[] = $url;
		return static::postOnShutdownIfApiRequest("open-url", ['url' => $url]);
	}
	/**
	 * @param string $command
	 * @return array
	 */
	public static function execute(string $command): ?array{
		return static::postOnShutdownIfApiRequest("command", ['command' => $command]);
	}
	/**
	 * @param string $path
	 * @param array $data
	 * @return void
	 */
	private static function postOnShutdownIfApiRequest(string $path, array $data): ?array{
		ConsoleLog::debug(__METHOD__);
		if(AppMode::isApiRequest()){
			static::postOnShutdown($path, $data);
			return null;
		} else{
			return static::post($path, $data);
		}
	}
	/**
	 * @param string $path
	 * @param array $data
	 * @return void
	 */
	private static function postOnShutdown(string $path, array $data): void{
		ConsoleLog::debug(__METHOD__);
		register_shutdown_function(function() use ($data, $path){
			static::post($path, $data);
		});
	}
	/**
	 * @param string $path
	 * @param array $data
	 * @return array
	 */
	private static function post(string $path, array $data): array{
		ConsoleLog::debug(__METHOD__);
		$url = self::WEBHOOK_BASE . "/$path";
		$response = LusitanianGuzzleClient::post($url, $data);
		if($response->getStatusCode() !== 201){
			le("$url failed: ", $response->getReasonPhrase());
		}
		$body = json_decode($response->getBody()->getContents(), true);
		if(self::successfulEmptyResponse($body, $path, $data)){
            return $body;
        }
		QMLog::print($body, "POST $path");
		return $body;
	}
	/**
	 * @param $body
	 * @param string $path
	 * @param array $data
	 * @return bool
	 */
	private static function successfulEmptyResponse($body, string $path, array $data): bool{
		$err = $body[0]["stderr"] ?? null;
		$iconError = $err && str_contains($err, 'Icon theme');
		if($iconError){
			unset($body[0]["stderr"]);
			$err = null;  // too much spam from calling xdg-open
		}
		if($err && !$iconError){
			le($err . " from $path with post data: " . QMLog::print_r($data, true));
		}
		if(!$err && array_key_exists("stdout", $body[0]) && empty($body[0]["stdout"]) &&
		   isset($body[0]["exitCode"]) && $body[0]["exitCode"] === 0){
			return true;
		}
		return false;
	}
}
