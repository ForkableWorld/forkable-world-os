<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors;
use App\DataSources\HasUserProfilePage;
use App\DataSources\OAuth2Connector;
use App\UI\FontAwesome;
use Illuminate\Support\Arr;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\OAuth2\Token\StdOAuth2Token;
/** Class LinkedInConnector
 * @package App\DataSources\Connectors
 */
class LinkedInConnector extends OAuth2Connector{
	use HasUserProfilePage;
	/**
	 * Defined scopes
	 *
	 * @link https://docs.microsoft.com/en-us/linkedin/shared/authentication/authorization-code-flow?context=linkedin/context
	 */
	const SCOPE_R_LITEPROFILE       = 'r_liteprofile';
	const SCOPE_R_FULLPROFILE       = 'r_fullprofile';
	const SCOPE_R_EMAILADDRESS      = 'r_emailaddress';
	const SCOPE_R_NETWORK           = 'r_network';
	const SCOPE_R_CONTACTINFO       = 'r_contactinfo';
	const SCOPE_RW_NUS              = 'rw_nus';
	const SCOPE_RW_COMPANY_ADMIN    = 'rw_company_admin';
	const SCOPE_RW_GROUPS           = 'rw_groups';
	const SCOPE_W_MESSAGES          = 'w_messages';
	const SCOPE_W_MEMBER_SOCIAL     = 'w_member_social';
    protected const AFFILIATE = false;
    protected const BACKGROUND_COLOR = '#351313';
    protected const CLIENT_REQUIRES_SECRET = true;
    protected const DEFAULT_VARIABLE_CATEGORY_NAME = 'Social Interactions';
    protected const DEVELOPER_CONSOLE = 'https://linkedin.com/settings/applications/new';
    
    
	public const DISPLAY_NAME = 'LinkedIn';
	protected const ENABLED = 1;
	protected const GET_IT_URL = 'https://linkedin.com/';
	public const IMAGE = 'https://static.quantimo.do/img/connectors/linkedin-connector.png';
	protected const LOGO_COLOR = '#0e76a8';
	protected const LONG_DESCRIPTION = 'Manage your professional identity. Build and engage with your professional network. Access knowledge, insights and opportunities.';
	protected const SHORT_DESCRIPTION = 'Tracks social interaction.';
	
	
	//protected const SOCIALITE_NAME = 'LinkedIn';
	public $affiliate = self::AFFILIATE;
	public $backgroundColor = self::BACKGROUND_COLOR;
	public $clientRequiresSecret = self::CLIENT_REQUIRES_SECRET;
	public $defaultVariableCategoryName = self::DEFAULT_VARIABLE_CATEGORY_NAME;
	public $displayName = self::DISPLAY_NAME;
	public $enabled = self::ENABLED;
    public $fontAwesome = FontAwesome::LINKEDIN;
	public $getItUrl = self::GET_IT_URL;
	public $id = self::ID;
	public $image = self::IMAGE;
	public $logoColor = self::LOGO_COLOR;
	public $longDescription = self::LONG_DESCRIPTION;
	public $name = self::NAME;
	public $providesUserProfileForLogin = true;
    public $importViaApi = false;
	public $shortDescription = self::SHORT_DESCRIPTION;
	public const ID = 82;
    public const NAME = 'linkedin';
    public static $OAUTH_SERVICE_NAME = 'Linkedin';
    public static array $SCOPES = ['r_liteprofile', 'r_emailaddress'];
    /**
     * The fields that are included in the profile.
     * @var array
     */
    protected $profileFields = [
        'id',
        'first-name',
        'last-name',
        'formatted-name',
        'email-address',
        'headline',
        'location',
        'industry',
        'public-profile-url',
        'picture-url',
        'picture-urls::(original)',
    ];
    /**
     * @return void
     */
    public function importData(): void {}
    /**
     * @return string
     */
    public function urlUserDetails(): string{
        //$fields = implode(',', $this->profileFields);
        //$url = 'https://api.linkedin.com/v1/people/~:('.$fields.')';
        $url = 'https://api.linkedin.com/v2/me?projection=(id,firstName,lastName,profilePicture(displayImage~:playableStreams))';
        return $url;
    }
	/**
	 * Get the email address for the user.
	 * @return array
	 */
    protected function getEmailAddress(): array{
        $url = 'https://api.linkedin.com/v2/emailAddress?q=members&projection=(elements*(handle~))';
	    $response = $this->getRequest($url, [], ['X-RestLi-Protocol-Version' => '2.0.0',]);
	    $arr = (array) json_decode(json_encode($response), true);
        $arr = (array) Arr::get($arr, 'elements.0.handle~');
        return $arr["emailAddress"];
    }
	/**
	 * @param array $response
	 * @return array
	 */
	protected function parseUserResponse(array $response): array{
        $preferredLocale = Arr::get($response, 'firstName.preferredLocale.language') . '_' . Arr::get($response, 'firstName.preferredLocale.country');
        $firstName = Arr::get($response, 'firstName.localized.' . $preferredLocale);
        $lastName = Arr::get($response, 'lastName.localized.' . $preferredLocale);
        $images = (array) Arr::get($response, 'profilePicture.displayImage~.elements', []);
        $avatar = Arr::first($images, function ($image) {
            return $image['data']['com.linkedin.digitalmedia.mediaartifact.StillImage']['storageSize']['width'] === 100;
        });
        $originalAvatar = Arr::first($images, function ($image) {
            return $image['data']['com.linkedin.digitalmedia.mediaartifact.StillImage']['storageSize']['width'] === 800;
        });
        $email = $this->getEmailAddress();
        return [
            'id' => $response['id'],
            'nickname' => null,
            'name' => $firstName.' '.$lastName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' =>$email,
            'avatar' => Arr::get($avatar, 'identifiers.0.identifier'),
            'avatar_original' => Arr::get($originalAvatar, 'identifiers.0.identifier'),
        ];
    }
    public function getUserProfilePageUrl():?string{
        $u = $this->getUser();
        return $u->getUserMetaValue($this->name.'_public_profile_url');
    }
	/**
	 * {@inheritdoc}
	 */
	public function getAuthorizationEndpoint()
	{
		return new Uri('https://www.linkedin.com/oauth/v2/authorization');
	}
	/**
	 * {@inheritdoc}
	 */
	public function getAccessTokenEndpoint()
	{
		return new Uri('https://www.linkedin.com/oauth/v2/accessToken');
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
	protected function parseAccessTokenResponse($responseBody): StdOAuth2Token
	{
		$data = json_decode($responseBody, true);
		if (!is_array($data)) {
			throw new TokenResponseException('Unable to parse response.');
		} elseif (isset($data['error'])) {
			throw new TokenResponseException('Error in retrieving token: "' . $data['error'] . '"');
		}
		return $this->newStdOAuth2Token($data);
	}
}
