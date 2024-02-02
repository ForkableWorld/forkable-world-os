<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings;
use App\AppSettings\AdditionalSettings\MonetizationSettings;
use App\AppSettings\AppDesign\Menu\MenuItem;
use App\Buttons\Links\AboutUsButton;
use App\Buttons\RelationshipButtons\Application\ApplicationCollaboratorsButton;
use App\Buttons\States\ConfigurationStateButton;
use App\DataSources\QMClient;
use App\Exceptions\ClientNotFoundException;
use App\Exceptions\InvalidClientIdException;
use App\Exceptions\QMFileNotFoundException;
use App\Exceptions\UnauthorizedException;
use App\Files\FileHelper;
use App\Models\Application;
use App\Models\Collaborator;
use App\Models\OAAccessToken;
use App\Models\OAClient;
use App\Models\User;
use App\Properties\Application\ApplicationAppTypeProperty;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseClientSecretProperty;
use App\Properties\Base\BaseUserIdProperty;
use App\Repos\ApplicationSettingsRepo;
use App\Slim\Controller\AppSettings\GetAppSettingsController;
use App\Slim\Controller\OAuth2\GetAuthorizationPageController;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Auth\QMAccessToken;
use App\Slim\Model\DBModel;
use App\Slim\Model\Reminders\AnonymousReminder;
use App\Slim\Model\Reminders\QMTrackingReminder;
use App\Slim\Model\User\QMUser;
use App\Slim\View\Request\QMRequest;
use App\Storage\DB\Writable;
use App\Storage\Memory;
use App\Storage\QMFileCache;
use App\Types\QMStr;
use App\Utils\AppMode;
use App\Utils\UrlHelper;
use App\Variables\VariableSearchResult;
/** Class AppSettings
 * @package App\AppSettings
 */
class AppSettings extends BaseApplication {
    public const APP_BUILDER_URL = "https://builder.quantimo.do";
    public const ENCRYPTION_KEY = "thesuperawesomesecretforencryption101";
    public const TABLE = 'applications';
    public const FIELD_ID = 'id';
    public const FIELD_ORGANIZATION_ID = 'organization_id';
    public const FIELD_CLIENT_ID = 'client_id';
    public const FIELD_APP_DISPLAY_NAME = 'app_display_name';
    public const FIELD_APP_DESCRIPTION = 'app_description';
    public const FIELD_LONG_DESCRIPTION = 'long_description';
    public const FIELD_USER_ID = 'user_id';
    public const FIELD_ICON_URL = 'icon_url';
    public const FIELD_TEXT_LOGO = 'text_logo';
    public const FIELD_SPLASH_SCREEN = 'splash_screen';
    public const FIELD_HOMEPAGE_URL = 'homepage_url';
    public const FIELD_APP_TYPE = 'app_type';
    public const FIELD_APP_DESIGN = 'app_design';
    public const FIELD_CREATED_AT = 'created_at';
    public const FIELD_UPDATED_AT = 'updated_at';
    public const FIELD_DELETED_AT = 'deleted_at';
    public const FIELD_ENABLED = 'enabled';
    public const FIELD_STRIPE_ACTIVE = 'stripe_active';
    public const FIELD_STRIPE_ID = 'stripe_id';
    public const FIELD_STRIPE_SUBSCRIPTION = 'stripe_subscription';
    public const FIELD_STRIPE_PLAN = 'stripe_plan';
    public const FIELD_LAST_FOUR = 'last_four';
    public const FIELD_TRIAL_ENDS_AT = 'trial_ends_at';
    public const FIELD_SUBSCRIPTION_ENDS_AT = 'subscription_ends_at';
    public const FIELD_COMPANY_NAME = 'company_name';
    public const FIELD_COUNTRY = 'country';
    public const FIELD_ADDRESS = 'address';
    public const FIELD_STATE = 'state';
    public const FIELD_CITY = 'city';
    public const FIELD_ZIP = 'zip';
    public const FIELD_PLAN_ID = 'plan_id';
    public const FIELD_EXCEEDING_CALL_COUNT = 'exceeding_call_count';
    public const FIELD_EXCEEDING_CALL_CHARGE = 'exceeding_call_charge';
    public const FIELD_STUDY = 'study';
    public const FIELD_BILLING_ENABLED = 'billing_enabled';
    public const FIELD_OUTCOME_VARIABLE_ID = 'outcome_variable_id';
    public const FIELD_PREDICTOR_VARIABLE_ID = 'predictor_variable_id';
    public const FIELD_PHYSICIAN = 'physician';
    public const FIELD_ADDITIONAL_SETTINGS = 'additional_settings';
    public const FIELD_APP_STATUS = 'app_status';
    public const FIELD_BUILD_ENABLED = 'build_enabled';
    private $userIsCollaboratorOrAdmin;
    public $additionalSettings;
    public $appDesign;
    public $appStatus;
    public $billingEnabled;
    public $buildEnabled;
    public $builderUrl;
    public $clientSecret;
    public $collaborators;
    public $id;
    public $privateConfig;
    public $redirectUri;
    public $authorizationUrl;
    public $users;
    public const LARAVEL_CLASS = Application::class;
    protected $client;
	/**
	 * AppSettings constructor.
	 * @param \App\Models\Application|null $l
	 */
    public function __construct(Application $l = null){
        if(!$l){return;}
		$this->laravelModel = $l;
        $row = QMStr::convertPropertiesToCamelCase($l->attributesToArray());
        parent::__construct($row);
        $this->appDesign = $row->appDesign = new AppDesign($row);
        $this->additionalSettings = $row->additionalSettings = new AdditionalSettings($row);
        $this->appDescription = $row->appDescription ?? null;
        if(!QMRequest::getQueryParam('designMode') && !QMRequest::isPost()){
            $this->appDesign->removeCustomAppDesignProperties();
        }
		// This should be done in the controller
        //$this->addUserAndCollaboratorDesignModeProperties();
        $this->appDisplayName = $row->appDisplayName ?? null;
        if(empty($this->displayName) && !empty($this->appDisplayName)){
            $this->displayName = $this->appDisplayName;
        }
        $this->appStatus = new AppStatus($row);
        $this->appType = ApplicationAppTypeProperty::pluckOrDefault($row);
        $this->buildEnabled = isset($row->buildEnabled) && $row->buildEnabled;
        $this->clientId = $row->clientId ?? null;
        self::removeDeprecatedProperties($this);
        if(!isset($this->homepageUrl)){$this->homepageUrl = AboutUsButton::QM_INFO_URL;}
        MonetizationSettings::checkPlayPublicLicenseKey($this);
        $this->getAppIcon();
        $this->getBuilderUrl();
        MenuItem::checkMenuItems($this);
        $nonAppSettingsRequest = AppMode::isApiRequest() && !QMRequest::urlContains('appSettings'); // Avoids checking collaborator table in DB
        if($nonAppSettingsRequest || !$this->getUserIsCollaboratorOrAdmin()){
            $this->getAdditionalSettings()->setBuildSettings(null);
        }
        $this->getNameAttribute();
        $this->getImage();
    }
    public function getNameAttribute(): string {
        $n = $this->name;
        if(!$n){
            $n = $this->getClientId();
        }
		if(!$n){le('!$n');}
        return $this->name = $n;
    }
    /**
     * @return string|null
     */
    public function getAppIcon(): ?string{
        $icon = $this->getAdditionalSettings()->getAppImages()->appIcon;
        if(empty($icon) && $this->getClientId() === BaseClientIdProperty::CLIENT_ID_QUANTIMODO){
            $this->logErrorOrDebugIfTesting("No app icon!  Please update it at ".$this->getBuilderUrl());
        }
        return $icon;
    }
	/**
	 * @param string|null $clientId
	 * @return AppSettings|null
	 * @throws ClientNotFoundException
	 */
    public static function getByClientId(string $clientId = null): ?AppSettings{
        return self::getClientAppSettings($clientId);
    }
    public static function crowdsourcingCures(): AppSettings{
        try {
            return static::find(BaseClientIdProperty::CLIENT_ID_CROWDSOURCING_CURES);
        } catch (ClientNotFoundException $e) {
            le($e);
        }
    }
    /**
     * @param string|int $nameOrId
     * @return AppSettings
     * @throws ClientNotFoundException
     */
    public static function find($nameOrId): ?DBModel{
        if(is_int($nameOrId)){
            return Application::findInMemoryOrDB($nameOrId)->getDBModel();
        }
        return Application::getByClientId($nameOrId);
    }
    public static function qm(): AppSettings {
        try {
            return static::find(BaseClientIdProperty::CLIENT_ID_QUANTIMODO);
        } catch (ClientNotFoundException $e) {
            le($e);
        }
    }
	/**
	 * @param string|null $clientID
	 * @return AppSettings|null
	 */
	public static function findByClientId(?string $clientID): ?AppSettings{
		try {
			return static::getClientAppSettings($clientID);
		} catch (ClientNotFoundException $e) {
			return null;
		}
	}
    /**
     * @return string
     */
    public function getTitleAttribute(): string{
        if(!empty($this->appDisplayName)){
            $this->displayName = $this->appDisplayName;
        }
        if(!empty($this->displayName)){
            $this->appDisplayName = $this->displayName;
        }
        return $this->displayName;
    }
    /**
     * @return string
     */
    public function getBuilderUrl(): string{
        return $this->builderUrl = Application::getAppDesignerLink($this->getClientId());
    }
    /**
     * @return QMUser[]
     */
    public function getCollaboratorUsers(): array {
        if($this->collaborators !== null){return $this->collaborators;}
		$owner = $this->getQmUser()->l();
	    $users = User::query()
			->join(Collaborator::TABLE, Collaborator::TABLE.'.user_id', "=", User::TABLE.".ID")
			->where(Collaborator::FIELD_USER_ID, "<>", $owner->getId())
			->where(Collaborator::TABLE.".client_id", $this->clientId)
			->get();
		$users[] = clone $owner;
	    foreach($users as $collaborator){
		    $QMUser = $collaborator->getQMUser();
		    $QMUser = clone $QMUser; // Avoid unsetting accessToken if they're a user as well
		    $QMUser->unsetPasswordAndTokenProperties();
		    $collaborators[] = $QMUser;
	    }
        return $this->collaborators = $collaborators;
    }
	/**
	 * @param string $clientId
	 * @param string|null $clientSecret
	 * @return object
	 * @throws ClientNotFoundException
	 * @noinspection PhpMissingReturnTypeInspection
	 */
    public static function getPrivateConfigTemplate(string $clientId, string $clientSecret = null){
        if($clientSecret === self::ENCRYPTION_KEY || (!$clientSecret &&
		        Collaborator::userIsCollaboratorOrAdmin($clientId))){
            $clientRow = OaClient::findInMemoryOrDB($clientId);
            if(!$clientRow){
                throw new ClientNotFoundException($clientId);
            }
            $clientSecret = $clientRow->client_secret;
        }
        $privateConfig = self::getDefaultPrivateConfig(false);
        $privateConfig = str_replace([
            '__QUANTIMODO_CLIENT_ID__',
            '__QUANTIMODO_CLIENT_SECRET__'
        ], [
            $clientId,
            $clientSecret
        ], $privateConfig);
        $privateConfig = json_decode($privateConfig, false);
        return $privateConfig;
    }
    /**
     * @param bool $jsonDecode
     * @return bool|mixed|string
     */
    public static function getDefaultPrivateConfig(bool $jsonDecode = true){
        $pathToConfig = FileHelper::projectRoot().'/data/default-configs/private_config_template.json';
        $privateConfig = file_get_contents($pathToConfig);
        if($jsonDecode){$privateConfig = json_decode($privateConfig, false);}
        return $privateConfig;
    }
	/**
	 * @return null|string
	 */
	public function getRedirectUri(): ?string{
		return $this->redirectUri = $this->getClient()->getRedirectUri();
	}
	/**
	 * @return string
	 */
	public function getAuthorizationUrl(): string{
		return $this->authorizationUrl = GetAuthorizationPageController::generateAuthorizeUrl($this->getClientId(),
			$this->getRedirectUri());
	}
	/**
     * @param int|User|QMUser $u
     * @return bool
     */
    public function isOwner($u): bool {
        $userId = BaseUserIdProperty::pluckOrDefault($u);
        return $this->getUserId() === $userId;
    }
    /**
     * @param string|null $clientSecret
     */
    public function setClientSecret(?string $clientSecret): void{
        $this->clientSecret = $clientSecret;
    }
    /**
     * @param $quantimodoClientId
     * @return string
     */
    private static function getAppSettingsMemcachedKey($quantimodoClientId): string{
        return $quantimodoClientId.'_app_settings';
    }
	/**
	 * @param string|null $clientId
	 * @return AppSettings|null
	 * @throws ClientNotFoundException
	 */
    public static function getClientAppSettings(string $clientId = null): ?AppSettings{
        if (!$clientId) {$clientId = BaseClientIdProperty::fromRequest(false);}
        if(empty($clientId)){throw new ClientNotFoundException("No client id!");}
        $clientId = BaseClientIdProperty::replaceWithQuantiModoIfAlias($clientId);
        BaseClientIdProperty::validateClientId($clientId);
        $fromMemory = Memory::getClientAppSettings($clientId);
        if($fromMemory !== null){  // Avoid DB query if false
            if ($fromMemory === false) {throw new ClientNotFoundException($clientId);}
            return $fromMemory;
        }
        $app = Application::findByClientId($clientId);
        if (!$app) {
            Memory::setClientAppSettings($clientId, false);
            throw new ClientNotFoundException($clientId);
        }
        $appSettings = new AppSettings($app);
        Memory::setClientAppSettings($clientId, $appSettings);
        return $appSettings;
    }
    /**
     * @return string
     */
    public function getImage(): string{
        $image = $this->image;
        if(empty($image)){
            $as = $this->getAdditionalSettings();
            $image = $as->getAppImages()->getAppIcon();
        }
		if(!$image){le('!$image');}
        return $this->image = $image;
    }
	/**
	 * @return void
	 * @throws \App\Exceptions\InvalidStringException
	 */
    protected function saveToFile(): void {
        ApplicationSettingsRepo::saveAppSettings($this->clientId, $this);
    }
    /**
     * @param string $clientId
     * @param int $maxAge
     * @return AppSettings|null
     */
    public static function getFromFile(string $clientId, int $maxAge = 86400): ?AppSettings {
        try {
            return ApplicationSettingsRepo::getAppSettings($clientId, $maxAge);
        } catch (QMFileNotFoundException $e) {
            return null;
        }
    }
    /**
     * @return bool
     */
    public static function setIsMobile(): bool{
        $isMobile = QMClient::isMobile();
        Memory::set(Memory::IS_MOBILE, $isMobile);
        return $isMobile;
    }
    /**
     * @param $appSettings
     */
    private static function removeDeprecatedProperties($appSettings): void{
        unset($appSettings->additionalSettings->appStorageIdentifier);
    }
	/**
	 * @param string $clientId
	 */
    public static function deleteAppSettingsFromMemcached(string $clientId): void{
        QMFileCache::delete(self::getAppSettingsMemcachedKey($clientId));
    }
    /**
     * @return AppSettings[]
     */
    public static function getAllBuildableAppSettings(): array{
        $qb = Writable::addWhereNotEmptyDbClauses(BaseApplication::readonly(), [
            'additional_settings',
            'app_design',
            'app_status'
        ]);
        $applicationRows = $qb->getArray();
        $allAppSettingsArray = [];
        foreach($applicationRows as $applicationRow){
            $appSettings = new AppSettings($applicationRow);
            //if($appSettings->deleteIfTestAppCreatedMoreThan2DaysAgo()){continue;}
            $allAppSettingsArray[$appSettings->displayName] = $appSettings;
        }
        return $allAppSettingsArray;
    }
	/**
	 * @param int|null $userId
	 * @return AppSettings
	 * @throws UnauthorizedException
	 */
    public static function getAllWhereUserIsCollaborator(int $userId = null): array{
        if(!$userId){
            if(!QMAuth::getQMUser()){
                QMAuth::throwUnauthorizedException("Not authenticated");
            }
            $userId = QMAuth::id();
        }
		$ids = Collaborator::whereUserId($userId)->pluck(Collaborator::FIELD_APP_ID);
        $qb = Application::whereIn(Application::FIELD_ID, $ids);
        $applicationRows = $qb->get();
		$appIds = $applicationRows->pluck(Application::FIELD_ID);
		$diff = $ids->diff($appIds);
        $allAppSettingsArray = [];
        foreach($applicationRows as $row){
            $appSettings = new AppSettings($row);
            //if($appSettings->deleteIfTestAppCreatedMoreThan2DaysAgo()){continue;}
            $allAppSettingsArray[] = $appSettings;
        }
        return $allAppSettingsArray;
    }
    /**
     * @param array $params
     * @return array
     */
    public static function get(array $params = []): array{
        $qb = Writable::addWhereClauses(static::readonly(), $params, static::TABLE);
        return static::instantiateArray($qb->getArray());
    }
    /**
     * @return AppDesign
     */
    public function getAppDesign(): AppDesign{
        $design = $this->appDesign;
	    if(!$design instanceof AppDesign){$design = new AppDesign($this);}
        return $this->appDesign = $design;
    }
    /**
     * @return AppStatus
     */
    public function getAppStatus(): AppStatus{
        $data = $this->appStatus;
        if(!$data instanceof AppStatus){$data = new AppStatus($this);}
        return $this->appStatus = $data;
    }
	/**
	 * @param string $clientId
	 * @return string
	 */
    public static function getAppDesignerLink(string $clientId): string{
        return UrlHelper::getBuilderUrl($clientId);
    }
    /**
     * @param string $clientId
     * @return string
     */
    public static function getIntegrationGuideLink(string $clientId): string{
	    $appUrl = \App\Utils\Env::getAppUrl();
	    if(!str_contains($appUrl, 'local.')){
            $h = $appUrl;
        } else {
            $h = "https://app.quantimo.do";
        }
        return $h."/account/apps/$clientId/integration";
    }
    /**
     * @param array $data
     * @param string|null $reason
     * @return int
     */
    public function softDelete(array $data = [], string $reason = null): int {
        return QMClient::softDeleteAllForClientId($this->getClientId(), $reason);
    }
    /**
     * @return AdditionalSettings\AppIds
     */
    public function getAppIds(): AdditionalSettings\AppIds{
        return $this->getAdditionalSettings()->getAppIds();
    }
    /**
     * @return User[]
     */
    public function setUsers(): array{
        $clientId = $this->clientId;
        $users = Application::getUsersByClientId($clientId);
		$users[] = QMUser::demo();
        return $this->users = $users;
    }
    /**
     * @return QMUser[]
     */
    public function getUsers(): array{
        if($this->users !== null){
            return $this->users;
        }
        return $this->setUsers();
    }
    public function getDebugButtons(): array{
        $buttons = [];
        $buttons[] = new ConfigurationStateButton($this);
        $buttons[] = new ApplicationCollaboratorsButton($this->l());
        return $buttons;
    }
	/**
	 * @return void
	 * @throws UnauthorizedException
	 */
    public function addUserAndCollaboratorDesignModeProperties(): void {
	    if(!$this->getUserIsCollaboratorOrAdmin()){
		    $u = QMAuth::getQMUser();
		    $string = $u ? $u->getLoginNameAndIdString() : "Unidentified user";
		    $buttons = $this->getDebugButtons();
		    $this->logError("$string is not authorized to get users and collaborators of app.  ", [
			    'buttons' => $buttons,
		    ]);
		    return;
	    }
	    // TODO: Update builder to fetch users as-needed
	    $this->getUsers(); // URGENT Don't get all users for every
	    $this->getCollaboratorUsers();  // Make a separate request for these
	    $this->getAuthorizationUrl();
	    $this->setClientSecret($this->getClient()->client_secret);
    }
    /**
     * @return void
     */
    private function setUserIsCollaboratorOrAdmin(): void{
	    $this->userIsCollaboratorOrAdmin = Collaborator::userIsCollaboratorOrAdmin($this->clientId);
    }
    /**
     * @return bool
     */
    private function getUserIsCollaboratorOrAdmin(): bool{
        if($this->userIsCollaboratorOrAdmin === null){
            $this->setUserIsCollaboratorOrAdmin();
        }
        return $this->userIsCollaboratorOrAdmin;
    }
    /**
     * @param array|null $meta
     * @return array
     */
    public function getLogMetaData(?array $meta = []):array {
        $meta['debug_url'] = UrlHelper::getLocalUrl("/api/v1/appSettings?clientId=".$this->clientId);
        return $meta;
    }
    /**
     * @return string
     */
    public function getLogMetaDataString(): string {
        return $this->clientId.": ";
    }
    /**
     * @return string
     */
    public function getClientSecret(): string{
        return $this->clientSecret = $this->getClient()->client_secret;
    }
    /**
     * @return bool
     */
    public function getBillingEnabled(): bool{
        return $this->billingEnabled ?: false;
    }
	/**
	 * @return void
	 */
    public function writeStaticData(): void{
        $staticData = new StaticAppData($this->getClientId(), $this);
        $staticData->writeAppSettingsToLocalFile();
    }
    /**
     * @param QMUser $user
     * @return QMTrackingReminder[]
     */
    public function createReminders(QMUser $user): array {
        $reminderSettings = $this->getAppDesign()->getDefaultTrackingReminderSettings();
        if($reminderSettings){
            $reminders = $reminderSettings->createReminders($user);
            return $reminders;
        }
        return [];
    }
    /**
     * @return AnonymousReminder[]
     */
    public function getReminders(): array{
        return $this->getAppDesign()->getDefaultTrackingReminderSettings()->getActive();
    }
    /**
     * @param string|null $clientId
     * @return string
     */
    public static function getBuilderLinkSentence(string $clientId = null): string {
        if($clientId){
            return "You can manage your app at ".UrlHelper::getBuilderUrl($clientId).". ";
        }
        return "Please provide your client_id from ". self::APP_BUILDER_URL.". ";
    }
    /**
     * @return VariableSearchResult
     */
    public function getPrimaryOutcomeVariable(): VariableSearchResult{
        $design = $this->getAppDesign();
        $v = $design->getPrimaryOutcomeVariable();
        return $v;
    }
    /**
     * @param string $string
     * @return string
     */
    public function replaceAliases(string $string): string {
        $a = $this->getAppDesign()->getAliases()->getActive();
        foreach ($a as $original => $alias) {
            $original = str_replace('Alias', '', $original);
            $string = str_replace($original, $alias, $string);
            $string = str_replace(ucfirst($original), ucfirst($alias), $string);
        }
        return $string;
    }
    public function getClient(): OAClient {
        if($this->client){return $this->client;}
        return $this->client = OAClient::findInMemoryOrDB($this->getClientId());
    }
    /**
     * @param string|null $clientId
     * @return array
     */
    public static function getUsersByClientId(string $clientId): array{
        $qb = OAAccessToken::whereClientId($clientId)
                ->groupBy([OAAccessToken::FIELD_USER_ID])
                ->orderBy(OAAccessToken::FIELD_UPDATED_AT, "DESC");
        QMAccessToken::addExpirationWhereClause($qb);
        $qb->limit(50);
        $tokens = $qb->get();
		if(!$tokens->count()){return [];}
	    $userIds = $tokens->pluck(OAAccessToken::FIELD_USER_ID)->all();
		$users = User::findManyInMemoryOrDB($userIds);
		$users = QMUser::toDBModels($users);
		foreach($users as $user){
			$t = $tokens->firstWhere(OAAccessToken::FIELD_USER_ID, $user->getId());
			if(!$t){le($user);}
			$user->unsetPasswordAndTokenProperties();
			$user->accessToken = $t->access_token;
			$user->scope = $t->scope;
		}
	    return $users;
    }
    /**
     * @return Application
     */
    public function l(): Application{
	    /** @noinspection PhpIncompatibleReturnTypeInspection */
	    return parent::l();
    }
    public function getSubtitleAttribute(): string{
        return $this->appDescription;
    }
    public function getUrl(array $params = []): string{
        return $this->getHomepageUrl();
    }
	public function getApplication(): Application{
		return $this->l();
	}
}
