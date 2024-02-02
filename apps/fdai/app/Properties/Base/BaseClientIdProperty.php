<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection SpellCheckingInspection */
namespace App\Properties\Base;
use App\DataSources\Connectors\MyNetDiaryConnector;
use App\DataSources\QMClient;
use App\Exceptions\ClientNotFoundException;
use App\Exceptions\InvalidClientIdException;
use App\Exceptions\UnauthorizedException;
use App\Http\Urls\IntendedUrl;
use App\Models\Application;
use App\Models\OAAccessToken;
use App\Models\OAClient;
use App\Models\User;
use App\PhpUnitJobs\JobTestCase;
use App\Properties\BaseProperty;
use App\Properties\Study\StudyIdProperty;
use App\Repos\CCStudiesRepo;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Auth\QMAccessToken;
use App\Slim\Model\User\QMUser;
use App\Slim\View\Request\QMRequest;
use App\Storage\Memory;
use App\Traits\ForeignKeyIdTrait;
use App\Traits\HasHeaderValue;
use App\Traits\HasPatients;
use App\Traits\PropertyTraits\AdminProperty;
use App\Types\PhpTypes;
use App\Types\QMStr;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\Utils\AppMode;
use App\Utils\Env;
use App\Utils\Subdomain;
use App\Utils\UrlHelper;
use OpenApi\Generator;
class BaseClientIdProperty extends BaseProperty{
    use ForeignKeyIdTrait, AdminProperty, HasHeaderValue;
    public const CLIENT_ID_CLINICAL_TRIALS_GOV = "clinical-trials-gov";
    public const CLIENT_ID_CROWDSOURCING_CURES = CCStudiesRepo::USERNAME;
    public const CLIENT_ID_CURE_TOGETHER = "ct";
    public const CLIENT_ID_DEMO = 'demo';
    public const CLIENT_ID_FDA = 'FDA';
    public const CLIENT_ID_FNDDS = 'FNDDS';  // Supplements
    public const CLIENT_ID_GHOST_INSPECTOR = 'ghostinspector';
    public const CLIENT_ID_JACK_HANDEY = 'jack_handey';
    public const CLIENT_ID_LARAVEL = 'laravel';
    public const CLIENT_ID_MONEYMODO = 'moneymodo';
    public const CLIENT_ID_OAUTH_TEST_CLIENT = 'oauth_test_client';
    public const CLIENT_ID_QUANTIMODO = 'quantimodo';
    public const CLIENT_ID_QUANTIMODO_DELETE_ME = 'delete_me';
    public const CLIENT_ID_QUANTIMODO_LARAVEL = 'QuantiModoLaravel';
    public const CLIENT_ID_SYSTEM = 'system';
    public const CLIENT_ID_UNKNOWN = 'unknown';
    public const CLIENT_ID_USDA = 'USDA';
    public const CLIENT_ID_WEB = 'web';
    public const PHYSICIAN_CLIENT_ID_PREFIX = "user-";
	const STUDY_SUFFIX = "-study";
	public const TEST_CLIENT_ID_PREFIX = 'test-app';
	public const HEADER_NAME = "X-CLIENT-ID";
	public $dbInput = 'string,255:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = "The ID of the client application that created this record.";
	public $example = self::CLIENT_ID_OAUTH_TEST_CLIENT;
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::CARD;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::CLIENT_ID;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 80;
	public $minLength = 2;
    public const NAME = OAClient::FIELD_CLIENT_ID;
    public $name = self::NAME;
	public $canBeChangedToNull = false;
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:80';
	public $title = 'Client';
	public $type = PhpTypes::STRING;
	public $validations = 'nullable|max:80';
	public const SYNONYMS = [
        'quantimodo_client_id',
        QMClient::FIELD_CLIENT_ID,
        'clientid'
    ];
	public $shouldNotContain = [
        'googleusercontent.com'
    ];
    public const QUANTIMODO_ALIAS_CLIENT_IDS = [
        'angularjs-embeddable',
        'api',
        'app',
        'builder',
        BaseClientIdProperty::CLIENT_ID_DEMO,
        //'physician',
        'dev-builder',
        //'dev-physician',
        Subdomain::DEV_WEB,
	    Subdomain::FEATURE,
        'developer',
        'docs',
        'dr',
        'import',
        self::CLIENT_ID_LARAVEL,
        Env::ENV_LOCAL,
        Env::ENV_PRODUCTION,
        'qm-staging',
        BaseClientIdProperty::CLIENT_ID_QUANTIMODO,
        Env::ENV_STAGING,
        'staging-api',
        'staging-web',
        'studies',
        'utopia',
        BaseClientIdProperty::CLIENT_ID_WEB,
        'web-staging',
        Env::ENV_TESTING,
        'www',
	    BaseClientIdProperty::CLIENT_ID_UNKNOWN,
    ];
	public static function addToUrlIfNecessary(string $url): string{
		if(AppMode::isApiRequest()){
			if($clientId = static::fromRequest(false)){
				if($clientId !== BaseClientIdProperty::CLIENT_ID_QUANTIMODO){
					$url = self::addToUrl($url, $clientId);
				}
			}
		}
		return $url;
	}
	public static function fromRequestDirectly(bool $throwException = false): ?string {
        if($id = self::fromHeader() ?? null){return $id;}
        return parent::fromRequestDirectly($throwException);
    }
	/**
	 * This method returns the client ID from the request. It first checks if the HTTP_X_NEWRELIC_SYNTHETICS header is
	 * set, and if so, it returns the CLIENT_ID_OAUTH_TEST_CLIENT. If not, it checks for the client ID in memory,
	 * then in the access token, then in the referrer, then in the referrer subdomain, and finally in the intended URL.
	 * If none of these are found, it throws a missing parameter exception if throwException is set to true,
	 * or returns null if not.
	 * @param bool $throwException Whether to throw an exception if the client ID is not found.
	 * @return ?string The client ID from the request.
	 * @throws UnauthorizedException
	 */
    public static function fromRequest(bool $throwException = false): ?string{
        $id = static::fromRequestDirectly(false);
        if(isset($_SERVER["HTTP_X_NEWRELIC_SYNTHETICS"]) || $id === 'newRelic'){
            return self::CLIENT_ID_OAUTH_TEST_CLIENT;
        }
        if(!$id){
            // fromMemory needs to go last or we end up getting from token instead of request
            if($mem = static::fromMemory()){return $mem;}
        }
        if(!$id){
            if($str = BaseAccessTokenProperty::fromRequest()){
                $t = QMAccessToken::find($str);
                if($t){$id = $t->getClientId();}
            }
        }
        if(!$id){$id = self::fromReferrer();}
	    if($id === "feature"){le("client id should not be feature");}
        if(!$id){$id = self::fromReferrerSubDomain();}
	    if($id === "feature"){le("client id should not be feature");}
        if(!$id){$id = self::fromIntendedUrl();}
	    if($id === "feature"){le("client id should not be feature");}
        if($id && str_contains($id, '.')){ // Necessary to avoid getting google client id
            $id = null;
            if($intended = IntendedUrl::get()){
                $id = UrlHelper::getQueryParam(QMClient::FIELD_CLIENT_ID, $intended);
            }
        }
		if(!$id){
			//$id = self::getPhysicianClientIdForLoggedInUser($id);
		}
        if($id === null && $throwException){static::throwMissingParameterException();}
        if($throwException && !$id && self::isRequiredForRequest()){
            QMAuth::throwUnauthorizedException('Please provide clientId with request!');
        }
		if(!$id){
			//return BaseClientIdProperty::CLIENT_ID_UNKNOWN;
            return null;
		}
        return BaseClientIdProperty::setInMemory($id);
    }
	/**
	 * @param null $data
	 * @return string|null
	 */
	public static function getDefault($data = null): ?string{
        return static::fromRequestJobOrSystem();
    }
	public static function isStudy(string $clientId): bool{
		return strpos($clientId, self::STUDY_SUFFIX) !== false;
	}
	/**
	 * @param string $studyClientId
	 */
	public static function validateCohortStudyClientId(string $studyClientId): void{
		if(stripos($studyClientId, StudyIdProperty::COHORT_STUDY_ID_SUFFIX) === false){
			throw new InvalidClientIdException($studyClientId,
				"Cohort study id's should have suffix " . StudyIdProperty::COHORT_STUDY_ID_SUFFIX . ". ");
		}
	}
	/**
     * @param array $data
     */
    public static function validateInNewUserArray(array $data){
        $clientId = $data[User::FIELD_CLIENT_ID] ?? null;
        if (empty($clientId)) {
            le("Please provide client id with new user data! ");
        }
        self::validateClientId($clientId);
    }
    /**
     * @return OAClient
     */
    public static function getForeignClass(): string{
        return OAClient::class;
    }
    /**
     * @param string $clientId
     * @return string
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public static function setInMemory($clientId): ?string{
        if(is_array($clientId)){
            $clientId = static::pluck($clientId);
        }
        // $clientId = QMClient::CLIENT_ID_QUANTIMODO; should be done in the getAppSettings function
        //if($clientId && UrlHelper::isQuantiModoAliasSubDomain($clientId)){$clientId = QMClient::CLIENT_ID_QUANTIMODO;}
        Memory::set(Memory::CLIENT_ID, $clientId, Memory::MISCELLANEOUS);
        return $clientId;
    }
    /**
     * @param null $default
     * @return string
     */
    public static function fromMemory($default = null): ?string{
        return Memory::get(Memory::CLIENT_ID, Memory::MISCELLANEOUS, $default);
    }
    /**
     * @return bool
     */
    public static function isDemo(): bool{
        $clientId = self::fromRequestDirectly();
        $result = $clientId && strtolower($clientId) === BaseClientIdProperty::CLIENT_ID_DEMO;
        return $result;
    }
    /**
     * @param string|null $clientId
     */
    public static function validateClientId(string $clientId = null){
        if($clientId === "Web"){
            throw new InvalidClientIdException($clientId);
        }
        if($clientId === "0"){
            throw new InvalidClientIdException($clientId);
        }
        if(!$clientId){
            throw new InvalidClientIdException($clientId);
        }
        if(strlen($clientId) < 2){
            throw new InvalidClientIdException($clientId, "Client id must be at least 2 characters long!");
        }
        if(str_contains($clientId, '.')){
            throw new InvalidClientIdException($clientId, "Client id cannot contain periods!");
        }
        if(strlen($clientId) > 79){
            throw new InvalidClientIdException($clientId, "Maximum client id length is 80 character!");
        }
    }
    /**
     * @param string $clientId
     * @param null $model
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public static function validateByValue($clientId, $model = null){
        self::validateClientId($clientId);
        if(strpos($clientId, '_')){
            throw new InvalidClientIdException($clientId,
                "Client id cannot ".
                "contain underscores because characters are not permitted in domain names in accordance with RFC 1035, ".
                "which only allows letters, digits and hyphens. As such, you cannot register a domain name with an underscore");
        }
        if(!filter_var($clientId. BaseUrlProperty::WILDCARD_APEX_DOMAIN, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)){
            throw new InvalidClientIdException($clientId,
                "$clientId is invalid. Your client id must be a ".
                "valid subdomain.  A domain name cannot exceed 255 octets (RFC 1034) and each label cannot exceed ".
                "63 octets (RFC 1035). It can contain any character (RFC 2181) but extra rules apply for hostnames ".
                "(A and MX records, data of SOA and NS records): only alphanumeric ASCII characters and hyphens are ".
                "allowed in labels.");
        }
    }
    /**
     * @return string
     */
    public static function fromReferrer(): ?string{
        if(!isset($_SERVER['HTTP_REFERER'])){
            return null;
        }
        $referrer = $_SERVER['HTTP_REFERER'];
        $parts = parse_url($referrer);
        if(isset($parts['query'])){
            parse_str($parts['query'], $query);
            if(isset($query['clientId'])){
                return $query['clientId'];
            }
            if(isset($query['client_id'])){
                return $query['client_id'];
            }
        }
        $subDomain = Subdomain::getSubDomainIfDomainIsQuantiModo($referrer);
        if($subDomain && BaseClientIdProperty::isQuantiModoAlias($subDomain)){
            return BaseClientIdProperty::CLIENT_ID_QUANTIMODO;
        }
        return $subDomain;
    }
    /**
     * @param string $clientId
     * @return string
     */
    public static function replaceWithQuantiModoIfAlias(string $clientId): string{
        if(self::isQuantiModoAlias($clientId)){
            return self::CLIENT_ID_QUANTIMODO;
        }
        return $clientId;
    }
    /**
     * @param string $clientId
     * @return bool
     */
    public static function isQuantiModoAlias(string $clientId): bool{
        return in_array($clientId, self::QUANTIMODO_ALIAS_CLIENT_IDS, false);
    }
    /**
     * @return bool
     */
    public static function isRequiredForRequest(): bool{
        if(Memory::get(Memory::LARAVEL, Memory::MISCELLANEOUS)){
            return false;
        }
        if(QMRequest::pathContains('.js')){
            return false;
        }
        if(QMRequest::onLaravelAPIPath()){
            return false;
        }
        return true;
    }
    public static function getMissingParameterErrorMessage(): string{
        $url = QMRequest::current();
        $url = UrlHelper::addParams($url, ['client_id' => "YOUR_CLIENT_ID_HERE"]);
        return "Please provide your QuantiModo client_id as a query parameter in your request. i.e $url ".
	        Application::getBuilderLinkSentence(null);
    }
    /**
     * @param string $clientId
     * @return string
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public static function sanitize($clientId): string{
        return QMStr::slugify($clientId, true); // Need to allow underscore for backward compatibility
    }
    /**
     * @return string
     */
    public static function fromReferrerSubDomain(): ?string{
        $referrer = QMRequest::getReferrer();
        if(!$referrer){
            return null;
        }
        $subDomain = Subdomain::getSubDomainIfDomainIsQuantiModo($referrer);
        if(in_array($subDomain, Subdomain::API_SUBDOMAINS)){
            return null;
        }
        return $subDomain;
    }
    /**
     * @return string
     */
    public static function fromRequestJobOrSystem(bool $throwException = false): string{
		$mem = self::fromMemory();
		if($mem){return $mem;}
        if(!AppMode::isApiRequest()){
            if($id = JobTestCase::getJobClientId()){
                return $id;
            }
            return self::CLIENT_ID_SYSTEM;
        }
        $id = BaseClientIdProperty::fromRequest($throwException);
        if(!$id){
            $id = self::CLIENT_ID_UNKNOWN;
        }
        return $id;
    }
    /**
     * @param string $clientId
     * @return bool
     */
    public static function isTestClientId(string $clientId): bool{
        if(strpos($clientId, 'test_app') !== false){
            return true;
        }
        if(strpos($clientId, 'test-app') !== false){
            return true;
        }
        return false;
    }
    /**
     * @return string
     */
    public static function getHostClientId(): string{
        $mem = Memory::getHostAppSettings();
        if($mem){
            return $mem->getClientId();
        }
        $url = QMRequest::current();
        if(!Subdomain::onQMAliasSubDomain() && $url){
            $subdomain = Subdomain::getSubDomainIfDomainIsQuantiModo($url);
            if($subdomain){
                return $subdomain;
            }
        }
		$clientId = Env::get('HOST_CLIENT_ID');
		if(!$clientId){
			$clientId = self::CLIENT_ID_QUANTIMODO;
		}
        return $clientId;
    }
    public static function setHostClientId(string $clientId): void {
        try {
            $as = Application::getByClientId($clientId);
        } catch (ClientNotFoundException $e) {
            le($e);
        }
        Memory::setHostAppSettings($as);
    }
    /**
     * @return string
     */
    public static function fromIntendedUrl(): ?string{
	    $url = IntendedUrl::get();
        if(!$url){return null;}
        if($client = UrlHelper::getQueryParam(self::NAME, $url)){return $client;}
        if($subDomain = Subdomain::getSubDomainIfDomainIsQuantiModo($url)){
            if(UrlHelper::isQMAliasSubDomain($subDomain)){
                return BaseClientIdProperty::CLIENT_ID_QUANTIMODO;
            }
            return $subDomain;
        }
        return null;
    }
    public static function isPhysicianClientId(string $clientId): bool {
        return strpos($clientId, self::PHYSICIAN_CLIENT_ID_PREFIX) === 0;
    }
    /**
     * @param User|QMUser|HasPatients $u
     * @return string
     */
    public static function generatePhysicianClientId($u): string {
        return self::PHYSICIAN_CLIENT_ID_PREFIX.$u->getLoginName();
    }
	/**
	 * @return string|null
	 */
	private static function getPhysicianClientIdForLoggedInUser(): ?string{
		if($user = QMAuth::getQMUserIfSet()){
			$physician = $user->getPhysicianClientId();
			$t = $user->oa_access_tokens()->where(OAAccessToken::FIELD_CLIENT_ID, "<>", $physician)
			          ->orderByDesc(OAAccessToken::CREATED_AT)->first();
			if($t){
				$id = $t->client_id;
			} else{
				$id = $user->client_id;
			}
		}
		return $id ?? null;
	}
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {return false;}
    public function showOnDetail(): bool {return false;}
    public static function getClientIdsWithPublicVariables(): array{
        return [
            self::CLIENT_ID_USDA,
            self::CLIENT_ID_FDA,
            self::CLIENT_ID_FNDDS, // Supplements
            MyNetDiaryConnector::NAME,
        ];
    }
    /**
     * @return string
     */
    public function getDefaultValue(): ?string{
        if($req = self::fromRequest()){return $req;}
        return parent::getDefaultValue();
    }
	protected static function getHeaderNames(): array{
		return [self::HEADER_NAME];
	}
	public static function fromRequestOrDefault(): string{
		$clientId = self::fromRequest();
		if($clientId){return $clientId;}
		return self::getHostClientId();
	}
}
