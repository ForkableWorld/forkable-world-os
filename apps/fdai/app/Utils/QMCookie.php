<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpComposerExtensionStubsInspection */
namespace App\Utils;
use App\Files\QMEncrypt;
use App\Http\Middleware\EncryptCookies;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Slim\QMSlim;
use Illuminate\Contracts\Encryption\DecryptException;
use Symfony\Component\HttpFoundation\Cookie;
class QMCookie extends Cookie {
	const DEFAULT_PATH = '/';
	const DEFAULT_DOMAIN = UrlHelper::API_APEX_DOMAIN;
	public const COOKIE_SESSION_LIFETIME_IN_SECONDS = 5 * 365 * 86400; // Laravel always makes them 5 years, so I'm just
	// being consistent
	const DEFAULT_SECURE = true;
	const DEFAULT_HTTP_ONLY = false;
	public static function all(): array{
		$decrypted = [];
		$original = $_COOKIE;
		if(!$original){
			$r = request();
			$original = $r->cookies->all();
		}
		foreach($original as $name => $value){
			if(!$value){
				continue;
			}
			try {
				$decrypted[$name] = self::decrypt($name, $value);
			} catch (DecryptException $e){
				$decrypted[$name] = $value;
			}
		}
		return $decrypted;
	}
	/**
	 * @param string $name
	 * @param $value
	 * @return mixed|string
	 */
	public static function decryptIfPossible(string $name, $value){
		try {
			if($decrypted = self::decrypt($name, $value)){
				return $decrypted;
			} else{
				//QMLog::info("Couldn't decrypt $value");
				return $value;
			}
		} catch (DecryptException $e){
		    //QMLog::info(__METHOD__.": ".$e->getMessage());
			return $value;
		}
	}
	public static function addSlimCookie(Cookie $cookie){
		if($i = QMSlim::getInstance()){
			$i->setCookie($cookie->getName(), self::encrypt($cookie->getName(), $cookie->getValue()),
				$cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(),
				$cookie->isHttpOnly());
		}
	}
	public static function generateExpirationTime(): int{
		return time() + self::COOKIE_SESSION_LIFETIME_IN_SECONDS;
	}
	/**
	 * @param string $name
	 * @param $value
	 * @return void
	 */
	public static function setCookie(string $name, $value): void{
		if(!AppMode::isApiRequest()){
			QMLog::debug("Not setting cookie in non-api request");
			return;
		}
		$cookie = new static($name, $value, self::generateExpirationTime(), self::DEFAULT_PATH, self::DEFAULT_DOMAIN,
			self::DEFAULT_SECURE, self::DEFAULT_HTTP_ONLY);
		self::addSlimCookie($cookie);
		if(function_exists('cookie')){
			cookie()->queue($cookie);
		}
//		setcookie($cookie->getName(), $value ? self::encrypt($name, $value) : '', $cookie->getExpiresTime(),
//			$cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
	}
	/**
	 * @param string $cookieName
	 */
	public static function deleteCookie(string $cookieName){
		unset($_COOKIE[$cookieName]);
		setcookie($cookieName, "", time() - 3600, self::DEFAULT_PATH, self::DEFAULT_DOMAIN, self::DEFAULT_SECURE,
			self::DEFAULT_HTTP_ONLY);
		setcookie($cookieName, "", time() - 3600);
		try {
			\Cookie::queue(\Cookie::forget($cookieName));
		} catch (\Throwable $e) {
		    ConsoleLog::error("Couldn't delete cookie with \Cookie::queue(\Cookie::forget($cookieName)) because " . 
		                      $e->getMessage());
		}
	}
	/**
	 * @param string $name
	 * @return null|string
	 */
	public static function getCookieValue(string $name): ?string{
		$session = session()->all();
		$laravelCookiesA = \Cookie::get();
		$valueLaravelA = $laravelCookiesA[$name] ?? null;
		$laravelCookiesB = request()->cookies->all();
		$valueLaravelB = $laravelCookiesA[$name] ?? null;
		$COOKIES = $_COOKIE ?? [];
		$valueCOOKIES = $laravelCookiesA[$name] ?? null;
		if($i = QMSlim::getInstance()){
			$slimCookies = $i->request->cookies->all();
			$valueSlim = $i->request->cookies($name);
			if($valueSlim){
				return self::decryptIfPossible($name, $valueSlim);
			}
		}
		$value = $valueLaravelA ?? $valueLaravelB ?? $valueCOOKIES;
		if(empty($value)){
			QMLog::debug("No " . $name . " cookie from _COOKIE");
		} else{
			QMLog::debug("Got " . $name . " cookie from _COOKIE");
		}
		return $value;
	}
	/**
	 * @param string $name
	 * @param string $value
	 * @return mixed
	 */
	public static function decrypt(string $name, string $value): string{
		if(in_array($name, EncryptCookies::EXCLUDE)){
			return $value;
		}
		return QMEncrypt::encryptor()->decrypt($value, EncryptCookies::serialized($name));
	}
	/**
	 * @param string $name
	 * @param string|null $value
	 * @return mixed
	 */
	public static function encrypt(string $name, ?string $value): string{
		if(in_array($name, EncryptCookies::EXCLUDE)){
			return $value;
		}
		return QMEncrypt::encryptor()->encrypt($value, EncryptCookies::serialized($name));
	}
}
