<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection MissingIssetImplementationInspection */
namespace App\DataSources;
use App\AppSettings\AdditionalSettings\DownloadLinks;
use App\AppSettings\AppSettings;
use App\AppSettings\HostAppSettings;
use App\DataSources\Connectors\QuantiModoConnector;
use App\Exceptions\BadRequestException;
use App\Exceptions\ClientNotFoundException;
use App\Exceptions\InvalidClientIdException;
use App\Exceptions\UnauthorizedException;
use App\Logging\QMLog;
use App\Models\Application;
use App\Models\Collaborator;
use App\Models\OAClient;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseClientSecretProperty;
use App\Properties\Base\BasePlatformProperty;
use App\Properties\User\UserIdProperty;
use App\Slim\Model\Auth\QMAccessToken;
use App\Slim\Model\Auth\QMRefreshToken;
use App\Slim\Model\DBModel;
use App\Slim\Model\Notifications\QMDeviceToken;
use App\Slim\Model\User\QMUser;
use App\Slim\View\Request\QMRequest;
use App\Storage\DB\QMDB;
use App\Storage\DB\Writable;
use App\Storage\Memory;
use App\Storage\S3\S3PublicApps;
use App\Traits\HasModel\HasUser;
use App\Types\ObjectHelper;
use App\UI\ImageUrls;
use App\Utils\AppMode;
use App\Utils\IonicHelper;
use App\Utils\UrlHelper;
use App\VariableCategories\EmotionsVariableCategory;
use App\VariableCategories\FoodsVariableCategory;
use App\VariableCategories\TreatmentsVariableCategory;
use App\Variables\QMVariableCategory;
use Illuminate\Support\Str;
use Throwable;
class QMClient extends QMDataSource {
	use HasUser;
    public const TABLE = OAClient::TABLE;
    public const FIELD_APP_IDENTIFIER = 'app_identifier';
    public const FIELD_CLIENT_ID = 'client_id';
    public const FIELD_CLIENT_SECRET = 'client_secret';
    public const FIELD_CREATED_AT = 'created_at';
    public const FIELD_DELETED_AT = 'deleted_at';
    public const FIELD_GRANT_TYPES = 'grant_types';
    public const FIELD_ICON_URL = 'icon_url';
    public const FIELD_ID = self::FIELD_CLIENT_ID;
    public const FIELD_REDIRECT_URI = 'redirect_uri';
    public const FIELD_UPDATED_AT = 'updated_at';
    public const FIELD_USER_ID = 'user_id';
    public const TEST_CLIENT_SECRET = BaseClientSecretProperty::TEST_CLIENT_SECRET;
    public const TYPE_APP = 'app';
    public const TYPE_PHYSICIAN = 'physician';
    public const TYPE_STUDY = 'study';
    public const LARAVEL_CLASS = OAClient::class;
    public $userId;
    public $clientId;
    public $clientSecret;
    public $redirectUri;
    public $grantTypes;
    public $appIdentifier;
    public $scopes;
	public $scope;  // TODO Remove this
    /**
     * QMClient constructor.
     * @param array $row
     */
    public function __construct($row = []){
        parent::__construct($row);
        $this->populate($row);
    }
	public static function default(): self {
		$id =  HostAppSettings::getHostClientId();
		return static::findInMemoryOrDB($id);
	}
	/**
	 * @return QMClient
	 * @throws ClientNotFoundException
	 */
	public static function fromRequest(): ?QMClient {
		$clientId = BaseClientIdProperty::fromRequest();
		if(!$clientId){throw new ClientNotFoundException($clientId);}
		$c = static::find($clientId);
		if(!$c){throw new ClientNotFoundException($clientId);}
		return $c;
	}
	public function getNameAttribute(): string{
        return $this->name = $this->getClientId();
    }
    /**
     * @return QMClient[]
     */
    public static function getQMClients(): array{
        if($clients = Memory::get(Memory::QM_CLIENTS,Memory::MISCELLANEOUS)){
            return $clients;
        }
        $clients = self::getQuantiModoClientArray();
        $clients = QMDataSource::processDataSources($clients);
        Memory::set(Memory::QM_CLIENTS, $clients,Memory::MISCELLANEOUS);
        return $clients;
    }
	/**
     * @param $clientId
     * @return QMClient
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public static function find($clientId): ?DBModel {
        $mem = static::findInMemory($clientId);
        if($mem){return $mem;}
        $row = OAClient::findInMemoryOrDB($clientId);
        if(!$row){return null;}
        $client = $row->getDBModel();
		if(!static::findInMemory($clientId)){le('!static::findInMemory($clientId)');}
        return $client;
    }
    /**
     * @return array
     */
    public static function getQuantiModoClientArray(): array{
        $clients = [
            35                                 => [
                'id'                          => 62,
                'name'                        => 'qmwp',
                'display_name'                => 'Quantimodo Wordpress Plugin',
                'image'                       => 'https://res.cloudinary.com/hmj5zmqze/image/upload/c_scale,w_120/v1446481805/quantimodo-logo-qm-rainbow_150x150_escved.jpg',
                'get_it_url'                  => 'https://quantimo.do/data-sources/quantimodo-wordpress-plugin/',
                'short_description'           => 'Tracks anything',
                'long_description'            => 'Collect, import, and display Quantified Self data in any page or post using a shortcode!',
                'enabled'                     => 0,
                'affiliate'                   => false,
                'defaultVariableCategoryName' => FoodsVariableCategory::NAME
            ],
            36                                 => [
                'id'                          => 63,
                'name'                        => 'moodimodoandroid',
                'display_name'                => 'MoodiModo for Android',
                'image'                       => 'https://i.imgur.com/xum4q3U.png?1',
                'get_it_url'                  => 'https://play.google.com/store/apps/details?id=com.moodimodo&hl=en',
                'short_description'           => 'Tracks mood and anything else. ',
                'long_description'            => 'MoodiModo tracking mood effortless using a unique pop-up reminder interface. This allows you to rate your mood in a fraction of a second at regular intervals.',
                'enabled'                     => 1,
                'affiliate'                   => false,
                'defaultVariableCategoryName' => EmotionsVariableCategory::NAME
            ],
            37                                 => [
                'id'                          => 64,
                'name'                        => 'moodimodoios',
                'display_name'                => 'MoodiModo for iOS',
                'image'                       => 'https://i.imgur.com/WLP30yg.png?1',
                'get_it_url'                  => 'https://itunes.apple.com/us/app/moodimodo/id1046797567?mt=8',
                'short_description'           => 'Tracks mood and anything else. ',
                'long_description'            => 'MoodiModo tracking mood effortless using a unique pop-up reminder interface. This allows you to rate your mood in a fraction of a second at regular intervals.',
                'enabled'                     => 1,
                'affiliate'                   => false,
                'defaultVariableCategoryName' => EmotionsVariableCategory::NAME
            ],
            38                                 => [
                'id'                          => 65,
                'name'                        => 'moodimodochrome',
                'display_name'                => 'MoodiModo Chrome Extension',
                'image'                       => 'https://i.imgur.com/yxwibDP.png?1',
                'get_it_url'                  => 'https://chrome.google.com/webstore/detail/moodimodo-beta/lncgjbhijecjdbdgeigfodmiimpmlelg',
                'short_description'           => 'Tracks mood and anything else. ',
                'long_description'            => 'MoodiModo tracking mood effortless using a unique pop-up reminder interface. This allows you to rate your mood in a fraction of a second at regular intervals.',
                'enabled'                     => 1,
                'affiliate'                   => false,
                'defaultVariableCategoryName' => EmotionsVariableCategory::NAME
            ],
            39                                 => [
                'id'                          => 66,
                'name'                        => 'qmchrome',
                'display_name'                => 'Quantimodo Chrome Extension',
                'image'                       => 'https://i.imgur.com/8UvLfY8.png',
                'get_it_url'                  => DownloadLinks::DEFAULT_CHROME_EXTENSION_DOWNLOAD_LINK,
                'short_description'           => 'Tracks anything.',
                'long_description'            => 'Use QuantiModo for Chrome to effortlessly track anything! You can track your sleep, diet, medication, physical activity, and anything else that can be quantified.',
                'enabled'                     => 1,
                'affiliate'                   => true,
                'defaultVariableCategoryName' => FoodsVariableCategory::NAME
            ],
            40                                 => [
                'id'                          => 67,
                'name'                        => 'qmsync',
                'display_name'                => 'Quantimodo Sync for Android',
                'image'                       => 'https://i.imgur.com/3QVZzdf.png',
                'get_it_url'                  => null,
                'short_description'           => 'Sync all your tracking data.',
                'long_description'            => 'QuantiModo Sync allows you to sync data from the following Android life-tracking apps to QuantiModo: Accupedo, Cardiograph, Good Day Journal, How Are You Feeling, MedHelper, MediSafe, myFitnessCompanion, MyFitnessPal, Sleep As Android, SportsTracker, T2MoodTracker, and ZombiesRun',
                'enabled'                     => 0,
                'affiliate'                   => false,
                'defaultVariableCategoryName' => FoodsVariableCategory::NAME
            ],
            42                                 => [
                'id'                          => 70,
                'name'                        => 'quantimodoandroid',
                'display_name'                => 'QuantiModo for Android',
                'image'                       => 'https://i.imgur.com/xum4q3U.png?1',
                'get_it_url'                  => DownloadLinks::DEFAULT_ANDROID_DOWNLOAD_LINK,
                'short_description'           => 'Tracks anything',
                'long_description'            => '',
                'enabled'                     => 0,
                'affiliate'                   => true,
                'defaultVariableCategoryName' => FoodsVariableCategory::NAME
            ],
            43                                 => [
                'id'                          => 71,
                'name'                        => 'quantimodoios',
                'display_name'                => 'QuantiModo for iOS',
                'image'                       => 'https://i.imgur.com/WLP30yg.png?1',
                'get_it_url'                  => DownloadLinks::DEFAULT_IOS_DOWNLOAD_LINK,
                'short_description'           => 'Tracks anything',
                'long_description'            => 'Easily track mood, symptoms, or any outcome you want to optimize in a fraction of a second.  You can also import your data from over 30 other apps and devices like Fitbit, Rescuetime, Jawbone Up, Withings, Facebook, Github, Google Calendar, Runkeeper, MoodPanda, Slice, Google Fit, and more.  QuantiModo then analyzes your data to identify which hidden factors are most likely to be influencing your mood or symptoms and their optimal daily values.',
                'enabled'                     => 0,
                'affiliate'                   => true,
                'defaultVariableCategoryName' => FoodsVariableCategory::NAME
            ],
            86                                 => [
                'id'                          => 86,
                'name'                        => "datasell",
                'display_name'                => 'DataSell',
                'image'                       => QMVariableCategory::getPayments()->imageUrl,
                'get_it_url'                  => 'https://datasell.quantimo.do',
                'short_description'           => 'Sell your data for research',
                'long_description'            => 'Sell your anonymous data to researchers from Fitbit, Withings, Google Fit, MyFitnessPal, Rescuetime, Facebook, and more.',
                'enabled'                     => 0,
                'affiliate'                   => true,
                'defaultVariableCategoryName' => TreatmentsVariableCategory::NAME,
                'background_color'            => '#23448b',
                'logo_color'                  => '#d34836',
                'client_requires_secret'      => false,
                'oauth_service_name'          => 'QuantiModo'
            ],
            QuantiModoConnector::ID => QMDataSource::getQuantiModoDataSourceArray(),
        ];
        foreach($clients as $client){
            $client['qm_client'] = true;
        }
        foreach($clients as $client){
            $client['dataSourceType'] = QMDataSource::TYPE_CLIENT_APP;
        }
        return $clients;
    }
    /**
     * @return AppSettings|null
     */
    public function getAppSettings(): ?AppSettings{
        try {
            $s = Application::getByClientId($this->getId());
        } catch (ClientNotFoundException $e){
            le($e);
        }
        if(!$s){return null;}
        return $s;
    }
    /**
     * @param string $clientId
     * @param string $clientSecret
     * @throws UnauthorizedException
     */
    public static function authenticateClient(string $clientId, string $clientSecret){
        $client = OAClient::findInMemoryOrDB($clientId);
        if(!$client || $client->client_secret !== $clientSecret){
            throw new UnauthorizedException('Client matching provided credentials not found');
        }
    }
    /**
     * @param string $clientId
     * @param string $reason
     * @return int
     */
    public static function delete(string $clientId, string $reason): int{
        //self::deleteAppFiles($clientId);  Not implemented yet!
        return self::softDeleteAllForClientId($clientId, $reason);
    }
    /**
     * @param string $clientId
     * @return void
     */
    private static function deleteAppFiles(string $clientId): void {
        QMLog::error("Deleting app files for $clientId");
        S3PublicApps::softDeleteDirectory($clientId);
    }
    /**
     * @param array $array
     * @return bool
     * @throws UnauthorizedException
     */
    public static function insert(array $array): bool{
        try {
            BaseClientIdProperty::validateClientId($array[self::FIELD_CLIENT_ID]);
        } catch (InvalidClientIdException $e) {
            if(!AppMode::isProductionApiRequest()){
                throw $e;
            }
        }
        return self::writable()->insert($array);
    }
	/**
     * @return bool
     */
    public static function isMobile(): bool {
        $platform = QMRequest::getPlatform();
        if($platform){
            if(stripos($platform, BasePlatformProperty::PLATFORM_WEB) !== false){return false;}
            if($platform === BasePlatformProperty::PLATFORM_IOS){return true;}
            if($platform === BasePlatformProperty::PLATFORM_ANDROID){return true;}
            if($platform === 'mobile'){return true;}
        }
        if(!isset($_SERVER['HTTP_USER_AGENT'])){return false;}
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        return (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $userAgent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($userAgent, 0, 4)));
    }
    /**
     * @return bool
     */
    public static function isChromeExtension(): bool{
        return stripos(QMRequest::getQueryParam('platform'), BasePlatformProperty::PLATFORM_CHROME) !== false;
    }
	/**
	 * @param array $appData
	 * @return Application
	 * @throws \App\Exceptions\InvalidEmailException
	 * @throws \App\Exceptions\NoEmailAddressException
	 */
    public function createApplication(array $appData = []): Application {
        $appData[self::FIELD_USER_ID] = $this->userId;
        $appData[self::FIELD_CLIENT_ID] = $this->clientId;
        if(!isset($appData[AppSettings::FIELD_APP_DISPLAY_NAME])){
            $appData[AppSettings::FIELD_APP_DISPLAY_NAME] = $this->getUser()->getDisplayNameAttribute();
        }
        $app = Application::createApplication($this->getUser(), $this->clientId, $appData);
        $app->firstOrCreateCollaborator($this->getUser());
        return $app;
    }
    /**
     * @param string $clientId
     * @param QMUser $user
     * @param array|string $redirectUris
     * @return bool|QMClient
     * @throws UnauthorizedException
     */
    public static function createClient(string $clientId, QMUser $user, $redirectUris = []){
        if(BaseClientIdProperty::isQuantiModoAlias($clientId)){
            throw new BadRequestException("$clientId is reserved.  Please try another one.");
        }
        $clientId = BaseClientIdProperty::sanitize($clientId);
        BaseClientIdProperty::validateByValue($clientId);
        $array = [
            self::FIELD_CLIENT_ID     => $clientId,
            self::FIELD_CLIENT_SECRET => Str::random(32),
            self::FIELD_USER_ID       => $user->id
        ];
        if(!empty($redirectUris)){
            $array[self::FIELD_REDIRECT_URI] = implode(' ', $redirectUris);
        }
        $bshafferClient = self::insert($array);
        if(isset($bshafferClient)){
            $client = new QMClient($array);
            return $client;
        }
        return false;
    }
    /**
     * @return QMUser
     */
    public function getQMUser(): QMUser{
        return QMUser::find($this->userId);
    }
    /**
     * @param $data
     */
    private function populate($data){
        foreach(ObjectHelper::getNonNullValuesWithCamelKeys($data) as $key => $value){
            $this->$key = $value;
        }
    }
    /**
     * @param $clientId
     * @param $userId
     * @return int
     */
    public static function revokeAccess(string $clientId, int $userId): int{
        QMRefreshToken::delete($clientId, $userId);
        return QMAccessToken::expire($clientId, $userId);
    }
    /**
     * @return bool
     */
    public static function frameworkIsIonicApp(): bool{
        if(!AppMode::isApiRequest()){
            return false;
        }
        $framework = QMRequest::getFramework();
        return $framework && stripos($framework, 'ionic') !== false;
    }
    /**
     * @param string $clientId
     * @return string
     */
    public static function replaceClientIdIfInvalid(string $clientId): ?string{
        if(!$clientId){return $clientId;}
        try {
            BaseClientIdProperty::validateClientId($clientId);
        } catch (InvalidClientIdException $e) {
            QMLog::error($e->getMessage() . " So using " .BaseClientIdProperty::CLIENT_ID_QUANTIMODO. " instead. ");
            return BaseClientIdProperty::CLIENT_ID_QUANTIMODO;
        }
        return $clientId;
    }
    /**
     * @param string $clientId
     * @param string $reason
     * @return int
     */
    public static function softDeleteAllForClientId(string $clientId, string $reason): int{
        Collaborator::softDeleteAllForClientId($clientId, $reason);
        QMDeviceToken::softDeleteAllForClientId($clientId, $reason);
        AppSettings::softDeleteAllForClientId($clientId, $reason);
        $qb = static::writable()->where(self::FIELD_CLIENT_ID, $clientId);
        return $qb->softDelete([], $reason);
    }
    /**
     * @param string|null $clientId
     * @return bool
     */
    public static function isLaravel(string $clientId = null): bool {
        if(!$clientId){
            $clientId = BaseClientIdProperty::fromRequest(false);
        }
        return $clientId === BaseClientIdProperty::CLIENT_ID_LARAVEL || $clientId ===
            BaseClientIdProperty::CLIENT_ID_QUANTIMODO_LARAVEL;
    }
    /**
     * @param array $params
     * @return array
     */
    public static function get(array $params = []): array{
        $qb = QMDB::addWhereClauses(static::readonly(), $params, static::TABLE);
        return static::instantiateArray($qb->getArray());
    }
	public function getRedirectUrl(): string {
		$url = $this->redirectUri;
		$first = explode(' ', $url)[0];
		if(str_contains($first, 'ionic/Modo/www/callback')){
			$url = IonicHelper::WEB_QUANTIMO_DO;
			$err = "$this->clientId Redirect URL is $url so redirecting to $url instead";
			QMLog::error($err);
			UrlHelper::addParam($url, 'error', $err);
			UrlHelper::addParam($url, 'quantimodo_client_id', $this->clientId);
			return $url;
		}
		return $first;
	}
	/**
     * @param string $newId
     */
    public function updateClientId(string $newId){
        $tables = Writable::getTableNamesWithColumn(self::FIELD_CLIENT_ID);
        Writable::disableForeignKeyConstraints();
        foreach($tables as $table){
            Writable::getBuilderByTable($table)
                ->where(self::FIELD_CLIENT_ID, $this->getClientId())
                ->update([self::FIELD_CLIENT_ID => $newId]);
        }
        Writable::enableForeignKeyConstraints();
    }
    public static function createSystemClients(){
        \App\Logging\ConsoleLog::info(__FUNCTION__);
        $constants = self::getConstants();
        $clientIds = BaseClientIdProperty::QUANTIMODO_ALIAS_CLIENT_IDS;
        foreach($constants as $constantName => $value){
            if(stripos($constantName, 'CLIENT_ID_') !== 0){continue;}
            $clientIds[] = $value;
        }
        $clientIds = array_unique($clientIds);
        foreach($clientIds as $clientId){
            try {
                OAClient::getOrCreate($clientId,
                    [OAClient::FIELD_USER_ID => UserIdProperty::USER_ID_SYSTEM]);
            } catch (Throwable $e) {
                QMLog::info(__METHOD__.": ".$e->getMessage());
            }
        }
    }
    /**
     * @return string
     */
    public function getId(): string{
        return $this->getClientId();
    }
    public function validateId(){
		if(!$this->clientId){le('!$this->clientId');}
    }
    /**
     * @param int|string $id
     * @return int|string
     */
    public function setId($id) {
        return $this->clientId = $id;
    }
    /**
     * @return string
     */
    public function getImage(): string{
        $image = $this->image;
		if($image && str_contains($image, '/.png')){
			QMLog::error("Client $this->clientId Image path contains invalid image /.png: $image");
			$this->image = null;
			$image = ImageUrls::BRAIN_96_PNG;
			$this->save();
			return $image;
		}
        if(empty($image)){$image = $this->getQmUser()->getAvatar();}
        return $this->image = $image;
    }
}
