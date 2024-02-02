<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors;
use App\DataSources\OAuth2Connector;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Token\TokenInterface;
class EbayConnector extends OAuth2Connector{
	protected const AFFILIATE = false;
	protected const BACKGROUND_COLOR = '#23448b';
	protected const CLIENT_REQUIRES_SECRET = false;
	protected const DEFAULT_VARIABLE_CATEGORY_NAME = 'Treatments';
	protected const DEVELOPER_CONSOLE = 'https://developer.ebay.com/my/keys';
	
	protected const DEVELOPER_USERNAME = 'rory_rocket';
	public const DISPLAY_NAME = 'Ebay';
	protected const ENABLED = 0;
	protected const GET_IT_URL = 'https://ebay.com';
	public const IMAGE = 'https://static.quantimo.do/img/connectors/ebay_logo.png';
	protected const LOGO_COLOR = '#d34836';
	protected const LONG_DESCRIPTION = 'Buy and sell electronics, cars, fashion apparel, collectibles, sporting goods, digital cameras, baby items, coupons, and everything else.';
	protected const OAUTH_SERVICE_NAME = 'Ebay';
	protected const SHORT_DESCRIPTION = 'Tracks purchases';
	
	
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
    public $oauthServiceName = self::OAUTH_SERVICE_NAME;
    public $shortDescription = self::SHORT_DESCRIPTION;
    public const ID = 85;
    public const NAME = 'ebay';
    public static $OAUTH_SERVICE_NAME = 'Ebay';
    public static array $SCOPES = [self::SCOPE_buy_order];
    public function importData(): void {

    }
	/**
	 * Scopes
	 *
	 * @var string
	 */
	const SCOPE_buy_order  = 'https://api.ebay.com/oauth/api_scope/buy.order.readonly';
	/**
	 * {@inheritdoc}
	 */
	public function getAuthorizationEndpoint()
	{
		return new Uri('https://auth.ebay.com/oauth2/authorize');
	}
	/**
	 * {@inheritdoc}
	 */
	public function getAccessTokenEndpoint()
	{
		return new Uri('https://api.ebay.com/identity/v1/oauth2/token');
	}
	/**
	 * {@inheritdoc}
	 */
	protected function getAuthorizationMethod(): int
	{
		return static::AUTHORIZATION_METHOD_HEADER_BEARER;
	}
	/**
	 * {@inheritdoc}
	 */
	protected function getExtraOAuthHeaders(): array{
		return [
			'Authorization' => 'Basic ' . base64_encode($this->getConnectorClientId() . ':' .
			                                            $this->getOrSetConnectorClientSecret())
		];
	}
	protected function parseAccessTokenResponse(string $responseBody): TokenInterface{
		return $this->parseStandardAccessTokenResponse($responseBody);
	}
}
