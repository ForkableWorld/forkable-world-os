<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Utils;
use App\Buttons\Analyzable\CreateStudyButton;
use App\Buttons\IonicButton;
use App\Buttons\QMButton;
use App\Buttons\States\HistoryAllStateButton;
use App\Buttons\States\ImportStateButton;
use App\Buttons\States\RemindersInboxStateButton;
use App\Buttons\States\RemindersManageStateButton;
use App\Buttons\States\SettingsStateButton;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Variable\VariableNameProperty;
use App\Slim\Model\States\IonicState;
use App\Slim\View\Request\QMRequest;
use App\Types\QMStr;
class IonicHelper {
	public const PATH_VARIABLE_SETTINGS = 'variable-settings';
	public const WEB_QUANTIMO_DO = 'https://web.quantimo.do/';
	public const APP_CROWDSOURCINGCURES_ORG = 'https://app.crowdsourcingcures.org/';
	public const PATIENTS_SUB_DOMAIN = 'patients';
	const IONIC_DEV_ORIGIN = "https://dev-web.quantimo.do";
	const IONIC_BASE_URL = self::WEB_QUANTIMO_DO;
	const CC_BASE_URL = self::APP_CROWDSOURCINGCURES_ORG;
	const RELATIVE_URL_PATH = 'app/public';
	/**
	 * @param string|null $clientIdSubDomain
	 * @param string $path
	 * @param array $params
	 * @return string
	 */
	public static function getIonicAppUrl(string $clientIdSubDomain = null, string $path = '', array $params = []): string{
		$url = IonicHelper::ionicOrigin($clientIdSubDomain);
		$url .= '/#/app/' . $path;
		$url = UrlHelper::addParams($url, $params);
		return $url;
		// Allowing anyone to change API with a URL param is a security risk return self::addStagingApiUrlIfNecessary
		//($url);
	}
	/**
	 * @param string $stateName
	 * @param array $params
	 * @param string|null $clientIdSubDomain
	 * @return string
	 */
	public static function getIonicAppUrlForState(string $stateName, array $params = [],
		string $clientIdSubDomain = null): string{
		$state = IonicState::getByName($stateName);
		$url = IonicHelper::ionicOrigin($clientIdSubDomain) . '#/app';
		$url .= $state->url;
		foreach($params as $key => $value){
			if(stripos($url, ":" . $key) !== false){
				$url = str_replace(":" . $key, urlencode($value), $url);
				unset($params[$key]);
			}
		}
		$url = UrlHelper::addParams($url, $params);
		//QMValidatingTrait::assertStringDoesNotContain($url, ['predictors/:', 'outcomes/:'], $stateName);
		return $url;
	}
	/**
	 * @param array $params
	 * @param string|null $clientIdSubDomain
	 * @return string
	 */
	public static function getStudyCreationUrl(array $params = [], string $clientIdSubDomain = null): string{
		$url = self::getIonicAppUrl($clientIdSubDomain, 'study-creation', $params);
		return $url;
	}
	/**
	 * @param array $params
	 * @param string|null $clientIdSubDomain
	 * @return string
	 */
	public static function getSettingsUrl(array $params = [], string $clientIdSubDomain = null): string{
		$url = self::getIonicAppUrl($clientIdSubDomain, 'settings', $params);
		return $url;
	}
	/**
	 * @param array $params
	 * @param string|null $clientIdSubDomain
	 * @return string
	 */
	public static function getHistoryUrl(array $params = [], string $clientIdSubDomain = null): string{
		$url = self::getIonicAppUrl($clientIdSubDomain, 'history-all-category/Anything', $params);
		return $url;
	}
	/**
	 * @param array $params
	 * @param string|null $clientIdSubDomain
	 * @return string
	 */
	public static function getChartsUrl(array $params = [], string $clientIdSubDomain = null): string{
		$variable = urlencode(VariableNameProperty::pluck($params));
		$path = 'charts/' . $variable;
		unset($params['variableName']);
		$url = self::getIonicAppUrl($clientIdSubDomain, $path, $params);
		return $url;
	}
	/**
	 * @param array $params
	 * @param string|null $clientIdSubDomain
	 * @return string
	 */
	public static function getChatUrl(array $params = [], string $clientIdSubDomain = null): string{
		$url = self::getIonicAppUrl($clientIdSubDomain, 'chat', $params);
		return $url;
	}
	/**
	 * @param array $params
	 * @param string|null $clientIdSubDomain
	 * @return string
	 */
	public static function getInboxUrl(array $params = [], string $clientIdSubDomain = null): string{
		$url = self::getIonicAppUrl($clientIdSubDomain, 'reminders-inbox', $params);
		return $url;
	}
	/**
	 * @param array $params
	 * @param string|null $clientIdSubDomain
	 * @return string
	 */
	public static function getIntroUrl(array $params = [], string $clientIdSubDomain = null): string{
		$url = self::getIonicAppUrl($clientIdSubDomain, 'intro', $params);
		return $url;
	}
	/**
	 * @param array $params
	 * @param string|null $clientIdSubDomain
	 * @return string
	 */
	public static function getReminderManagementUrl(array $params = [], string $clientIdSubDomain = null): string{
		$url = self::getIonicAppUrl($clientIdSubDomain, 'variable-list-category/Anything', $params);
		return $url;
	}
	public static function getReminderEditUrl(array $params = [], string $clientIdSubDomain = null): string{
		$url = self::getIonicAppUrl($clientIdSubDomain, 'reminder-add', $params);
		return $url;
	}
	/**
	 * @param string $accessToken
	 * @return string
	 */
	public static function getPatientHistoryUrl(string $accessToken): string{
		return "https://patient.quantimo.do/#/app/history-all-category/Anything?accessToken=$accessToken";
	}
	public static function getDevUrl(string $state = null): string{
		if($state){
			return self::IONIC_DEV_ORIGIN . "/index.html#/app/$state";
		}
		return self::IONIC_DEV_ORIGIN;
	}
	/**
	 * @return string
	 * @noinspection PhpUnused
	 */
	public static function getSearchUrl(): string{
		return self::getIonicAppUrl('web', 'search');
	}
	/**
	 * @return QMButton[]
	 */
	public static function getIonicButtons(): array{
		$buttons[] = new RemindersInboxStateButton();
		$buttons[] = new SettingsStateButton();
		$buttons[] = new HistoryAllStateButton();
		$buttons[] = new ImportStateButton();
		$buttons[] = new RemindersManageStateButton();
		$buttons[] = new CreateStudyButton();
		return $buttons;
	}
	/**
	 * @param string $url
	 * @return string
	 */
	public static function addStagingApiUrlIfNecessary(string $url): string{
		if(AppMode::isStaging()){
			$url = UrlHelper::addParam($url, 'apiUrl', 'staging.quantimo.do');
		}
		return $url;
	}
	/**
	 * @param string|null $clientIdSubDomain
	 * @return string
	 */
	public static function ionicOrigin(string $clientIdSubDomain = null): string{
		// Let's just use web.quantimo.do unless specified at higher level.
		// Otherwise, we end up with lots of weird client urls in WP study posts
		// Also, if necessary, clients can universally replace web.quantimo.do with their own urls.
		// Replacing a variety of urls server-side in WP post generation would be much more difficult
		if(!$clientIdSubDomain && AppMode::isApiRequest()){
			$clientIdSubDomain = BaseClientIdProperty::fromRequest(false);
		}
		$referrer = QMRequest::getReferrer();
		if($referrer && strpos($referrer, '#/app')){
			return QMStr::before('#/app/', $referrer);
		}
		if(empty($clientIdSubDomain)){
			return Env::getAppUrl()."/".self::RELATIVE_URL_PATH;
		}
		if($clientIdSubDomain === BaseClientIdProperty::CLIENT_ID_QUANTIMODO){
			return Env::getAppUrl()."/".self::RELATIVE_URL_PATH;
		}
		if(UrlHelper::isQMAliasSubDomain($clientIdSubDomain)){
			return 'https://' . $clientIdSubDomain . '.quantimo.do';
		}
		if($clientIdSubDomain === 'feature'){le("clientIdSubDomain is feature");}
		return 'https://' . $clientIdSubDomain . '.'.IonicButton::IONIC_WILDCARD_HOST;
	}
}
