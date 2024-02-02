<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Auth;
use App\Exceptions\InsufficientScopeException;
use App\Slim\Configuration\RouteConfiguration;
use App\Slim\QMSlim;
use OAuth2;
class OAuth2Server {
	/**
	 * @var OAuth2\Server
	 */
	private static $server;
	/**
	 * @return OAuth2\Server
	 * TODO: We currently can only request one scope at a time.  We need to allow a multiple simultaneous scope
	 * requests, specifically in the case of https://www.mashape.com/quantimodo/quantimodo
	 */
	private static function init(): OAuth2\Server{
		$grantTypes = [
			'authorization_code' => new OAuth2\GrantType\AuthorizationCode(QMSlim::getOAuthConnection()),
			'refresh_token' => new OAuth2\GrantType\RefreshToken(QMSlim::getOAuthConnection(),
				['always_issue_new_refresh_token' => true]),
		];
		//QMLog::debug('grantTypes are ', (array) $grantTypes);
		// Pass a storage object or array of storage objects to the OAuth2 server class
		// TODO look into state
		$config = [
			'enforce_state' => false,
			'allow_implicit' => true,
			'allow_public_clients' => true,
			'access_lifetime' => QMAccessToken::ACCESS_TOKEN_LIFETIME_IN_SECONDS,
			'auth_code_lifetime' => 600,
			// ten minutes
			'require_exact_redirect_uri' => false,
		];
		self::$server = new OAuth2\Server(QMSlim::getOAuthConnection(), $config, $grantTypes);
		//QMLog::debug('OAuth2Server server variable', (array) OAuth2Server::$server);
		// Configure the available scopes
		$defaultScope = 'basic';
		$supportedScopes = [
			'basic',
			RouteConfiguration::SCOPE_READ_MEASUREMENTS,
			'writemeasurements',
			'importdata',
		];
		$memory = new OAuth2\Storage\Memory([
			'default_scope' => $defaultScope,
			'supported_scopes' => $supportedScopes,
		]);
		//QMLog::debug('memory is', (array) $memory);
		$scopeUtil = new OAuth2\Scope($memory);
		//QMLog::debug('Json encode of the scopeUtil is ', (array) $scopeUtil);
		self::$server->setScopeUtil($scopeUtil);
		return self::$server;
	}
	/**
	 * @return OAuth2\Server
	 */
	public static function get(): OAuth2\Server{
		if(self::$server){
			return self::$server;
		}
		return self::init();
	}
	/**
	 * Check if everything in required scope is contained in available scope.
	 * @param string $required_scope - A space-separated string of scopes.
	 * @param string $available_scope - A space-separated string of scopes.
	 * @throws InsufficientScopeException
	 * @see http://tools.ietf.org/html/rfc6749#section-7
	 * @ingroup oauth2_section_7
	 */
	public static function checkScope(string $required_scope, string $available_scope){
		if(empty($required_scope)){
			return;
		}
		$required_scope_array = explode(' ', trim($required_scope));
		$available_scope_array = explode(' ', trim($available_scope));
		$valid = (count(array_diff($required_scope_array, $available_scope_array)) === 0);
		if(!$valid){
			throw new InsufficientScopeException("The request requires higher privileges (scope $required_scope required) " .
				"than provided by the access token (scope $available_scope associated with provided access token)");
		}
	}
}
