<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Exceptions\BadRequestException;
use App\Http\Urls\IntendedUrl;
use App\Models\User;
use App\Properties\BaseProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\User\QMUser;
use App\Slim\View\Request\QMRequest;
use App\Storage\Memory;
use App\Traits\PropertyTraits\AdminProperty;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\Types\QMArr;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\Utils\UrlHelper;
use OpenApi\Generator;
class BaseAccessTokenProperty extends BaseProperty{
	use IsString, AdminProperty;
    public const DEMO_TOKEN = 'demo';
	const URL_PARAM_NAME = 'quantimodoAccessToken';
	public const TEST_ACCESS_TOKEN_FOR_ANY_REFERRER_DOMAIN = 'test-token';
    public const SYNONYMS = [
		self::NAME,
		'quantimodo_access_token',
	    self::URL_PARAM_NAME,
		'accessToken',
		'sessionToken'
    ];
    public const PERMANENT_TEST_USER_ACCESS_TOKEN_FOR_18535 = '42ff4170172357b7312bb127fb58d5ea464943c1';
	const ADMIN_TEST_TOKEN = 'admin-test-token';
	public $dbInput = 'string,40';
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = 'access_token';
	public $example = '00007f1b054ab8e73978f8156fc8c26f637f0309';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::OAUTH_ACCESS_TOKEN;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::OAUTH_ACCESS_TOKEN;
	public $importance = false;
	public $isOrderable = false;
	public $isPrimary = true;
	public $isSearchable = true;
	public $maxLength = 40;
	public $name = self::NAME;
	public const NAME = 'access_token';
	public $phpType = PhpTypes::STRING;
	public $rules = 'required|max:40|unique:oa_access_tokens,access_token';
	public $title = 'Access Token';
	public $type = PhpTypes::STRING;
	public $validations = 'required|max:40|unique:oa_access_tokens,access_token';
	public static function addToUrlIfNecessary(string $url): string{
		if ($t = Memory::getQmAccessTokenObject()) {
			$url = UrlHelper::addParam($url, self::NAME, $t->accessToken);
		} else if ($u = QMAuth::getQMUserIfSet()) {
			$str = $u->getAccessTokenStringIfSet();
			if ($str) {
				$url = UrlHelper::addParam($url, self::NAME, $str);
			}
		}
		return $url;
	}
	/**
     * @return string
     * @throws BadRequestException
     */
    public static function fromRequest(bool $throwException = false): ?string{
        if($t = Memory::getQmAccessTokenObject()){return $t->accessToken;}
        $fromHeader = self::getAccessTokenStringFromBearerAuthorizationHeader();
	    if(QMRequest::getBodyParam('idToken')){  // accessToken is from Google in this case
		    $str = QMRequest::getParam([self::URL_PARAM_NAME]);
	    }else{
		    $str = QMRequest::getParam(self::SYNONYMS);
	    }
        if($fromHeader){
            if($str && $str !== $fromHeader){
                throw new BadRequestException("Access token from header does not match access token from body or request!");
            }
            Memory::setQmAccessTokenString($fromHeader);
            return $fromHeader;
        }
        if(is_array($str)){$str = QMArr::getValue($str, [self::NAME]);}
        if(!$str){$str = self::fromIntendedUrl();}
        if($str){Memory::setQmAccessTokenString($str);}
        return $str;
    }
    /**
     * @return string
     */
    public static function getAccessTokenStringFromBearerAuthorizationHeader(): ?string{
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        if($token){
            if(!str_starts_with($token, 'Bearer ')){
                throw new BadRequestException("access token provided in HTTP_AUTHORIZATION header should start with Bearer ");
            }
            $accessToken = str_replace('Bearer ', '', $token);
            if($accessToken && $accessToken !== "null" && $accessToken !== "undefined"){
                return $accessToken;
            }
        }
        return null;
    }
    /**
     * @return string
     */
    public static function fromIntendedUrl(): ?string{
        if($url = IntendedUrl::get()){
            if($token = UrlHelper::getQueryParam(self::URL_PARAM_NAME, $url)){
                return $token;
            }
        }
        return null;
    }
	/**
	 * @param User|QMUser $user
	 * @param string $url
	 * @return string
	 */
    public static function appendToUrl($user, string $url): string{
        $user = $user->l();
        $clientId = BaseClientIdProperty::fromRequestDirectly();
        $t = $user->getOrCreateAccessTokenString($clientId);
        if(!$t){$user->login();}
        $url = UrlHelper::addParam($url, self::URL_PARAM_NAME, $t);
        return $url;
    }
	public static function appendToUrlIfNecessary(string $url): string {
		if(stripos($url, \App\Utils\Env::getAppUrl()) !== 0){
			if(strpos($url, '/authorize?') === false){
				if($u = QMAuth::getQMUser()){
					$url = BaseAccessTokenProperty::appendToUrl($u, $url);
				}
			}
		}
		return $url;
	}
}
