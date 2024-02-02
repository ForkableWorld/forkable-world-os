<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors;
use App\DataSources\OAuth2Connector;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\Common\Token\TokenInterface;
class OpenHumansConnector extends OAuth2Connector{
    public const DISABLED_UNTIL = "2020-10-06";
    protected const ENABLED = 0;
    public const PATHS = [
    ];
    public static $BASE_API_URL = "https://www.openhumans.org/";
    protected const AFFILIATE = false;
    protected const BACKGROUND_COLOR = '#cc73e1';
    protected const CLIENT_REQUIRES_SECRET = false;
	protected const DEFAULT_VARIABLE_CATEGORY_NAME = 'Physical Activity';
	protected const DEVELOPER_CONSOLE = 'https://www.openhumans.org/direct-sharing/projects';
	public const DISPLAY_NAME = 'Open Humans';
	protected const LOGO_COLOR = '#4cc2c4';
	protected const LONG_DESCRIPTION = 'From genomes to GPS: you can explore data analyses, do citizen science, and donate data to researchers.';
	protected const PREMIUM = false;
	protected const SHORT_DESCRIPTION = 'Open Humans empowers people with their personal data.';
	public $affiliate = self::AFFILIATE;
	public $backgroundColor = self::BACKGROUND_COLOR;
	public $clientRequiresSecret = self::CLIENT_REQUIRES_SECRET;
	public $defaultVariableCategoryName = self::DEFAULT_VARIABLE_CATEGORY_NAME;
	public $displayName = self::DISPLAY_NAME;
	public $enabled = self::ENABLED;
	public $getItUrl = self::GET_IT_URL;
	public $id = self::ID;
	public $image = self::IMAGE;
	public $logoColor = self::LOGO_COLOR;
	public $longDescription = self::LONG_DESCRIPTION;
	public $name = self::NAME;
	public $premium = self::PREMIUM;
    public $shortDescription = self::SHORT_DESCRIPTION;
    public const ID = 97;
    public const IMAGE = 'https://i.imgur.com/WE8KUx7.png';
    public const NAME = 'open-humans';
    public static $OAUTH_SERVICE_NAME = 'OpenHumans';
    protected const GET_IT_URL = 'https://www.openhumans.org/';
    public static array $SCOPES = [
    ];
    public function importData(): void{
        $this->saveMeasurements();
    }
	protected function parseAccessTokenResponse(string $responseBody): TokenInterface{
		// TODO: Implement parseAccessTokenResponse() method.
	}
	/**
	 * @return UriInterface
	 */
	public function getAuthorizationEndpoint(): UriInterface{
		// TODO: Implement getAuthorizationEndpoint() method.
	}
	/**
	 * @return UriInterface
	 */
	public function getAccessTokenEndpoint(): UriInterface{
		// TODO: Implement getAccessTokenEndpoint() method.
	}
	/**
	 * Returns a class constant from ServiceInterface defining the authorization method used for the API
	 * Header is the sane default.
	 *
	 * @return int
	 */
	protected function getAuthorizationMethod(): int{
		return static::AUTHORIZATION_METHOD_HEADER_BEARER;
	}
}
