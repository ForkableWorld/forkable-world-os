<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Utils;
use App\Logging\QMLog;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\View\Request\QMRequest;
class Subdomain {
	const APP = 'app';
	public const DEV_WEB = 'dev-web';
	public const FEATURE = 'feature';
	const STAGING_REMOTE = Env::ENV_STAGING_REMOTE;
	const LOCAL = Env::ENV_LOCAL;
	const PRODUCTION_REMOTE = Env::ENV_PRODUCTION_REMOTE;
	const STAGING = Env::ENV_STAGING;
	public const TESTING = Env::ENV_TESTING;
	public const API_SUBDOMAINS = [
		self::PRODUCTION_REMOTE,
		self::STAGING_REMOTE,
		self::LOCAL,
		self::APP,
		self::STAGING,
		'utopia',
		self::FEATURE,
		'api',
		self::TESTING,
	];
	/**
	 * @param string $url
	 * @return string
	 */
	public static function getSubDomainIfDomainIsQuantiModo(string $url): ?string{
		if(stripos($url, 'quantimo.do') === false){
			QMLog::debug("Not using sub-domain because not on qm domain");
			return null;
		}
		$subDomain = Subdomain::getSubDomain($url);
		return $subDomain;
	}
	public static function is(string $value, string $url = null): bool{
		return self::getSubDomain($url) === $value;
	}
	/**
	 * @param string|null $url
	 * @return string
	 */
	public static function getSubDomain(string $url = null): string{
		if(!$url && QMRequest::host()){
			$url = QMRequest::host();
		}
		if(strpos($url, "http") === false){
			$url = "https://" . $url;
		}
		$parsedUrl = parse_url($url);
        if(!isset($parsedUrl['host'])){
            $exploded = explode("|", $url);
            if(isset($exploded[1]) && strpos($exploded[1], "http") === 0){
                $parsedUrl = parse_url($exploded[1]);
            }
            if(!isset($parsedUrl['host'])) {
                le("Could not parse url: " . $url . " parsed: " . \App\Logging\QMLog::print_r($parsedUrl, true));
            }
        }
		$host = explode('.', $parsedUrl['host']);
		return $host[0];
	}
	public static function isTesting(): bool{
		return self::is(Env::ENV_TESTING);
	}
	/**
	 * @return bool
	 */
	public static function onQMAliasSubDomain(): bool{
		$url = QMRequest::current();
		return in_array(self::getSubDomainIfDomainIsQuantiModo($url), BaseClientIdProperty::QUANTIMODO_ALIAS_CLIENT_IDS,
			true);
	}
	/**
	 * @param string $newSubdomain
	 * @param string|null $current
	 * @return string
	 */
	public static function replaceSubdomain(string $newSubdomain, string $current = null): string{
		if(!$current){
			$current = QMRequest::current();
		}
		$subDomain = self::getSubDomainIfDomainIsQuantiModo($current);
		if(!$subDomain){
			return $current;
		}
		return str_replace($subDomain, $newSubdomain, $current);
	}
}
