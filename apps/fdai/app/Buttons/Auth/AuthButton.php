<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Auth;
use App\Buttons\QMButton;
use App\Http\Urls\IntendedUrl;
use App\Logging\ConsoleLog;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
use App\Types\BoolHelper;
use App\Utils\Env;
use App\Utils\UrlHelper;
use Illuminate\Http\RedirectResponse;
abstract class AuthButton extends QMButton {
	public const PATH = '/auth';
	public function __construct(){
		parent::__construct($this->generateTitle());
		$this->link = Env::getAppUrl() . $this->getPath();
	}
	abstract protected function getPath(): string;
	/**
	 * @param array $params
	 * @return string
	 */
	public static function getRedirectUrl(array $params = []): string{
        try {
            // Don't do this!  It overwrites our previous one!
	        // IntendedUrl::setToCurrent();
        } catch (\Throwable $e) {
            ConsoleLog::exception($e);
            //le($e);
        }
		$reg = QMRequest::getParam('register');
		if(BoolHelper::isFalseyAndNotNull($reg)){
			$url = LoginButton::url($params);
		} else{
			$url = RegistrationButton::url($params);
		}
		$url = self::addParams($url);
		return $url;
	}
	public static function redirectToLoginOrRegister(): RedirectResponse {
		if(QMRequest::getBool('register')){
			return RegistrationButton::redirect();
		} else{
			return LoginButton::redirect();
		}
	}
	/**
	 * @param string $url
	 * @return string
	 */
	protected static function addParams(string $url, array $params = []): string{
		//$params = $_GET ?? [];
		$params[IntendedUrl::INTENDED_URL] = QMRequest::current();
		// Why? It breaks everything! $params['logout'] = true;
		$url = UrlHelper::addParams($url, $params);
		return $url;
	}
	abstract protected function generateImage(): string;
	abstract protected function generateFontAwesome(): string;
	abstract protected function generateTitle(): string;
	/**
	 * @return RedirectResponse
	 */
	public static function getRedirect($params = []): RedirectResponse{
		return redirect(static::getRedirectUrl($params));
	}
	public static function isAuthUrl(string $url): bool{
		$noQuery = UrlHelper::stripQuery($url);
		$strContains = str_contains($noQuery, "/auth/");
		return $strContains;
	}

    /**
     * @param array $params
     * @return RedirectResponse
     */
    public static function redirect(array $params = []): RedirectResponse
    {
		//if(EnvOverride::isLocal()){ApiTestFile::saveLocallyAndNotify();}
		IntendedUrl::setToCurrent();
		$url = static::url();
		$url = static::addParams($url, $params);
        return UrlHelper::redirect($url);
	}
	public static function logoutAndRedirect(string $reason){
		QMAuth::logout($reason);
		return static::redirect(['logout' => true]);
	}
	public static function shouldRedirectToLoginIfNotAuthenticated(\Illuminate\Http\Request $request = null): bool{
		$uri = $_SERVER["REQUEST_URI"] ?? null;
		$isGet = $uri && QMRequest::isGet();
		if($isGet){
			if(str_starts_with($uri, '/api/v2/')){return true;}
			if(str_starts_with($uri, '/api/oauth')){return true;}
			if(str_starts_with($uri, '/api/v1/oauth')){return true;}
			if(str_starts_with($uri, '/oauth')){return true;}
		}
		if($request){
			if($request->is('*/connect')){return false;}
			if($request->is('connect')){return false;}
			if($request->is('*/connectors/list')){return false;}
			if($request->expectsJson()){return false;}
			if($request->is('api/*')){return false;}
			if($request->ajax()){return false;}
			if(!$request->acceptsHtml()){return false;}
		}
		if($uri && str_starts_with($uri, '/api/')){return false;}
		return true;
	}
}
