<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources;
use App\AppSettings\AppSettings;
use App\Buttons\Links\AboutUsButton;
use App\Buttons\QMButton;
use App\Buttons\States\ImportStateButton;
use App\Buttons\States\RemindersInboxStateButton;
use App\Buttons\States\RemindersManageStateButton;
use App\Cards\DataSourceQMCard;
use App\Cards\QMCard;
use App\CodeGenerators\CodeGenerator;
use App\DataSources\Connectors\QuantiModoConnector;
use App\Exceptions\NotFoundException;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Models\Connector;
use App\Models\Variable;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\DBModel;
use App\Slim\Model\StaticModel;
use App\Slim\Model\User\QMUser;
use App\Storage\S3\S3Images;
use App\Traits\HasButton;
use App\Traits\HasSynonyms;
use App\Types\ObjectHelper;
use App\Types\QMArr;
use App\Types\QMStr;
use App\UI\ImageUrls;
use App\UI\IonIcon;
use App\UI\QMColor;
use App\Utils\APIHelper;
use App\Utils\EnvOverride;
use App\VariableCategories\SymptomsVariableCategory;
use App\Variables\QMVariable;
use App\Variables\QMVariableCategory;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use LogicException;
use RuntimeException;
/** Class QMDataSource
 * @package App\DataSources
 */
class QMDataSource extends DBModel {
	public const DATA_SOURCE_IMAGE_BASE_URL = S3Images::S3_IMAGE_URL."connectors/";
	use HasButton, HasSynonyms;
	private static $indexed;
	protected $crappy = false;
	protected $createRemindersForNewVariables;
	protected $mergeOverlappingMeasurements = false;
	protected static $anonymousDataSources;
	protected static $avoidDatabaseQueries;
	protected static $userDataSources;
	protected static array $ID_TO_NAME = [];
	public $affiliate;
	public $backgroundColor;
	public $buttons;
	public $card;
	public $connected;
	public $count;
	public $createdAt;
	public $dataSourceType;
	public $defaultUnitAbbreviatedName;
	public $defaultVariableCategoryName;
	public $displayName;
	public $errorMessage;
	public $enabled = true;
	public $fontAwesome;
	public $getItUrl;
	public $id;
	public $image;
	public $imageHtml;
	public $instructionsHtml;
	public $linkedDisplayNameHtml;
	public $logoColor;
	public $longDescription;
	public $name;
	public $numberOfConnections;
	public $numberOfConnectorImports;
	public $numberOfConnectorRequests;
	public $numberOfMeasurements;
	public $oauth = false;
	public $premium;
	public $shortDescription;
	public $synonyms = [];
	public $updatedAt;
	public $userId;
	public $wpPostId;
	public const FIELD_CLIENT_ID         = 'client_id';
	public const FIELD_CREATED_AT        = 'created_at';
	public const FIELD_DELETED_AT        = 'deleted_at';
	public const FIELD_DISPLAY_NAME      = 'display_name';
	public const FIELD_ENABLED           = 'enabled';
	public const FIELD_GET_IT_URL        = 'get_it_url';
	public const FIELD_ID                = 'id';
	public const FIELD_IMAGE             = 'image';
	public const FIELD_LONG_DESCRIPTION  = 'long_description';
	public const FIELD_NAME              = 'name';
	public const FIELD_OAUTH             = 'oauth';
	public const FIELD_QM_CLIENT         = 'qm_client';
	public const FIELD_SHORT_DESCRIPTION = 'short_description';
	public const FIELD_UPDATED_AT        = 'updated_at';
	public const INSTRUCTIONS_SUFFIX     = " Your data will automatically be imported and analyzed. ";
	public const LARAVEL_CLASS           = Connector::class;
	public const TABLE                   = QMConnector::TABLE;
	public const TYPE_CLIENT_APP         = 'client_app';
	public const TYPE_CONNECTOR          = 'connector';
	public const TYPE_spreadsheet_upload = 'spreadsheet_upload';
	/**
	 * QMDataSource constructor.
	 * @param $row
	 */
	public function __construct($row = null){
		$this->setImageIfEmpty();
		if(!$row){
			return;
		}
		$this->populateFieldsByArrayOrObject($row);
		if($this->displayName){$this->getLinkedDisplayNameHtml();}
		$this->addImageHtml();
		$this->setDefaultButtons();
		if(!$this->dataSourceType){
			$this->dataSourceType = self::TYPE_CLIENT_APP; // This default is overridden in child class constructors
		}
	}
	/**
	 * @param $nameOrId
	 * @param int|null $userId
	 * @return QMDataSource
	 * @throws NotFoundException
	 */
	public static function getByNameOrId($nameOrId, int $userId = null): QMDataSource{
		$res = self::getDataSourceByNameOrIdOrSynonym($nameOrId, $userId);
		if(!$res){
			throw new NotFoundException("Data source named $nameOrId not found!");
		}
		return $res;
	}
	/**
	 * @param int|string $nameOrId
	 * @return static
	 * @throws NotFoundException
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection
	 */
	public static function find($nameOrId): ?DBModel{
		return static::getByNameOrId($nameOrId);
	}
	/**
	 * @return bool
	 */
	public static function getAvoidDatabaseQueries(): bool{
		return self::$avoidDatabaseQueries ?? false;
	}
	/**
	 * @return static[]
	 */
	public static function getForRequest(): array{
		$sources = [];
		$loggedIn = QMAuth::getQMUser();
		$connectors = QMConnector::getConnectors();
		foreach($connectors as $c){
			if($c->isDisabled()){
				continue;
			}
			if(!$loggedIn && !$c->providesUserProfileForLogin){
				continue;
			}
			if($c->connection){
				$c->addConnectionInfo($c->connection); // Need to update in case connection status changed
			} else{
				$c->connected = false;
			}
			$c->addExtendPropertiesForRequest();
			$sources[] = $c;
		}
		if($loggedIn && APIHelper::apiVersionIsAbove(1)){
			$importers = $loggedIn->getSpreadsheetImporters();
			$sources = array_merge($sources, $importers);
		}
		$enabled = collect($sources)->where(QMConnector::FIELD_ENABLED, true);
		return array_values($enabled->toArray()); // Sometimes this gets decoded as an object so trying array_values
	}
	/**
	 * @return void
	 */
	public function setImageIfEmpty(): void{
		if(!$this->image && $this->name){ // Don't set for OAuth Clients
			$this->image = self::DATA_SOURCE_IMAGE_BASE_URL . $this->name . '.png';
		}
	}
	public static function idToName(int $id): ?string{
		return QMDataSource::$ID_TO_NAME[$id] ?? null;
	}
	/**
	 * @param string $errorMessage
	 */
	public function setErrorMessage(string $errorMessage): void{
		if(str_contains($errorMessage, 'storeCredentials ')){
			return;
		}
		$this->errorMessage = $errorMessage;
	}
	/**
	 * @param string $link
	 * @param string $id
	 * @return QMButton
	 */
	protected static function getItHereButton(string $link, string $id): QMButton{
		$button = new QMButton("Get it here!", $link, QMColor::HEX_GOOGLE_GREEN, IonIcon::bag);
		$button->id = "get-it-here-$id-button";
		$button->setImage(ImageUrls::SHOPPING_SHOPPING_CART);
		return $button;
	}
	/**
	 * @param QMDataSource[] $userDataSources
	 * @return QMDataSource[]
	 */
	public static function setUserDataSources(array $userDataSources): array{
		return self::$userDataSources = $userDataSources;
	}
	/**
	 * @param bool $avoidDatabaseQueries
	 */
	public static function setAvoidDatabaseQueries(bool $avoidDatabaseQueries){
		self::$avoidDatabaseQueries = $avoidDatabaseQueries;
	}
	/**
	 * @return QMDataSource[]
	 */
	public static function getAnonymousDataSources(): array{
		if(self::$anonymousDataSources){
			return self::$anonymousDataSources;
		}
		$QMConnectors = QMConnector::getAnonymousConnectors();
		$QMClients = QMClient::getQMClients();
		$dataSources = array_merge($QMConnectors, $QMClients);
		$spreadSheetUploaders = QMSpreadsheetImporter::get();
		$dataSources = array_merge($dataSources, $spreadSheetUploaders);
		return self::$anonymousDataSources = $dataSources;
	}
	/**
	 * @return QMDataSource[]
	 */
	public static function getUserDataSources(): array{
		return self::$userDataSources;
	}
	/**
	 * @return static[]
	 */
	public static function getEnabled(): array{
		$all = static::get();
		return collect($all)->where('enabled')->all();
	}
	/**
	 * @return bool
	 */
	public function isConnector(): bool{
		return $this->dataSourceType === self::TYPE_CONNECTOR;
	}
	/**
	 * @return bool
	 */
	public function isCrappyOrDisabled(): bool{
		if(!$this->enabled){
			return true;
		}
		return $this->crappy;
	}
	/**
	 * @return bool
	 */
	public function getCreateRemindersForNewVariables(): bool{
		return $this->createRemindersForNewVariables;
	}
	/**
	 * @param $rows
	 * @return QMDataSource[]|QMClient[]|QMConnector[]
	 */
	public static function processDataSources($rows): array{
		$dataSources = [];
		foreach($rows as $row){
			$dataSources[] = new self($row);
		}
		return $dataSources;
	}
	/**
	 * @param string|int $nameOrId
	 * @return bool|QMDataSource|QMSpreadsheetImporter
	 */
	public static function getDataSourceWithoutDBQuery($nameOrId){
		return static::getDataSourceByNameOrIdOrSynonym($nameOrId, null);
	}
	/**
	 * @param string|int $nameOrId
	 * @param int|null $userId
	 * @return static|null
	 */
	public static function getDataSourceByNameOrIdOrSynonym($nameOrId, int $userId = null){
		if(!$nameOrId){
			return null;
		}
		$i = $userId ?? 'anonymous';
		if(isset(self::$indexed[$i][$nameOrId])){
			return self::$indexed[$i][$nameOrId];
		}
		if($userId){
			$user = QMUser::find($userId);
			$sources = $user->getQMDataSources();
		} else{
			$sources = self::getAnonymousDataSources();
		}
		foreach($sources as $ds){
			if(!isset($ds->name)){
				le("no name in " . QMLog::var_export($ds, true));
			}
			if($ds->name === $nameOrId){
				return self::$indexed[$i][$nameOrId] = $ds;
			}
			if($ds->displayName === $nameOrId){
				return self::$indexed[$i][$nameOrId] = $ds;
			}
		}
		foreach($sources as $ds){
			if($ds->id === $nameOrId){
				return self::$indexed[$i][$nameOrId] = $ds;
			}
		}
		foreach($sources as $ds){
			if(is_string($nameOrId) && $ds->inSynonyms($nameOrId)){
				return self::$indexed[$i][$nameOrId] = $ds;
			}
		}
		QMLog::debug("Data source with nameOrId $nameOrId not found!");
		return self::$indexed[$i][$nameOrId] = false;
	}
	protected function addImageHtml(){
		if(!isset($this->getItUrl)){
			$this->getItUrl = "";
		}
		if(!isset($this->displayName)){
			$this->displayName = "";
		}
		$this->imageHtml = '<a href="' . $this->getUrl() . '"><img id="' . $this->getNameAttribute() . '_image" title="' .
			$this->getTitleAttribute() . '" src="' . $this->getImage() . '" alt="' . $this->getTitleAttribute() . '"></a>';
	}
	/**
	 * @return string
	 */
	public function getLinkedDisplayNameHtml(): string{
		if(!isset($this->displayName)){le("no display name in " . QMLog::print_r($this, true));}
		return $this->linkedDisplayNameHtml = '<a href="' . $this->getItUrl . '">' . $this->displayName . '</a>';
	}
	/**
	 * @param string|int|null $nameOrId
	 * @return QMDataSource
	 */
	public static function getAffiliatedQMDataSourceByNameOrId($nameOrId = null){
		if($nameOrId){
			$actualConnector = self::getDataSourceByNameOrIdOrSynonym($nameOrId);
			if(!$actualConnector || !is_object($actualConnector)){
				QMLog::error("Could not find data source named $nameOrId! Falling back to quantimodo data source");
				return self::getDataSourceByNameOrIdOrSynonym("QuantiModo");
			}
			if($actualConnector->affiliate){
				return $actualConnector;
			}
		}
		return self::getDataSourceByNameOrIdOrSynonym("QuantiModo");
	}
	/**
	 * @return QMButton[]
	 */
	public function setDefaultButtons(): array{
		return $this->setReminderButtons();
	}
	/**
	 * @return QMButton[]
	 */
	public function setReminderButtons(): array{
		$this->buttons = [];
		$this->buttons[] = new RemindersInboxStateButton();
		$this->buttons[] = new RemindersManageStateButton();
		return $this->buttons;
	}
	/**
	 * @param QMVariable|Variable $variable
	 * @param array $urlParams
	 * @return string
	 */
	public function setInstructionsHtml($variable, array $urlParams = []): string{
		return $this->setInboxInstructionsHtml($variable, $urlParams);
	}
	/**
	 * @param QMVariable|Variable $variable
	 * @param array $urlParams
	 * @return mixed
	 */
	public function setInboxInstructionsHtml($variable, array $urlParams = []): string{
		$name = $variable->getDisplayNameAttribute();
		$inbox = RemindersInboxStateButton::instance($urlParams);
		$inbox->setTextAndTitle("the reminder inbox here");
		$createReminderButton = $variable->getCreateReminderButton($urlParams);
		$createReminderButton->setTextAndTitle("Create a reminder for $name here");
		$createReminderPill = $createReminderButton->getChipSmall();
		$inboxPill = $inbox->getChipSmall();
		$img = null;
		$paragraph = "
			<h3 class='text-2xl font-extrabold dark:text-white'>
			$img
			Manual Recording Option
			</h3>
            <p class='my-4 text-xl text-gray-500'>
                $createReminderPill
                and record it daily by enabling notifications or using 
                $inboxPill.
            </p>
        ";
		//.QMDataSource::INSTRUCTIONS_SUFFIX;
		return $this->instructionsHtml = $paragraph;
	}
	/**
	 * @param array $urlParams
	 * @return string
	 */
	public function getConnectWebPageUrl(array $urlParams = []): string{
		$urlParams['connectorName'] = $this->name;
		return ImportStateButton::url($urlParams);
	}
	/**
	 * @return string
	 */
	public function getDefaultVariableCategoryName(): string{
		return $this->defaultVariableCategoryName;
	}
	/**
	 * @return QMVariableCategory
	 */
	public function getDefaultVariableCategory(): QMVariableCategory{
		return QMVariableCategory::find($this->getDefaultVariableCategoryName());
	}
	/**
	 * @return array
	 */
	public static function getQuantiModoDataSourceArray(): array{
		return [
			'id' => QuantiModoConnector::ID,
			'name' => BaseClientIdProperty::CLIENT_ID_QUANTIMODO,
			'display_name' => 'QuantiModo',
			'image' => 'https://static.quantimo.do/img/logos/quantimodo-logo-qm-rainbow-200-200.png',
			'get_it_url' => AboutUsButton::QM_INFO_URL,
			'short_description' => 'Tracks anything',
			'long_description' => 'QuantiModo allows you to easily track mood, symptoms, or any outcome you want to optimize in a fraction of a second.  You can also import your data from over 30 other apps and devices.  QuantiModo then analyzes your data to identify which hidden factors are most likely to be influencing your mood or symptoms.',
			'enabled' => 1,
			'affiliate' => false,
			'defaultVariableCategoryName' => SymptomsVariableCategory::NAME,
			'background_color' => '#e4405f',
			'client_requires_secret' => true,
		];
	}
	/**
	 * @return QMDataSource
	 */
	public static function getQuantiModoDataSource(): QMDataSource{
		return new QMDataSource(self::getQuantiModoDataSourceArray());
	}
	/**
	 * @param int $count
	 */
	public function setCount(int $count){
		$this->count = $count;
	}
	/**
	 * @return string
	 */
	public function getLogMetaDataString(): string{
		return "$this->name ";
	}
	/**
	 * @param $cached
	 * @return QMDataSource[]|QMConnector[]|QMSpreadsheetImporter[]|AppSettings[]
	 */
	public static function instantiateDataSources($cached): array{
		$allConverted = [];
		foreach($cached as $dataSource){
			if(ObjectHelper::isMongoOrStdClass($dataSource)){
				$converted = null;
				if(isset($dataSource->name)){
					$converted = self::getDataSourceByNameOrIdOrSynonym($dataSource->name);
				}
				if(!$converted && $dataSource->dataSourceType === self::TYPE_CLIENT_APP){
					$converted = new AppSettings($dataSource);
				}
				if(!$converted){
					$converted = new QMDataSource($dataSource);
				}
				$allConverted[] = $converted;
			} else{
				$allConverted[] = $dataSource;
			}
		}
		return $allConverted;
	}
	/**
	 * @return string
	 */
	public function getTitleAttribute(): string{
		$name = $this->displayName;
		if(empty($name)){
			$name = $this->getQmUser()->getTitleAttribute();
		}
		return $this->displayName = $name;
	}
	/**
	 * @return string
	 */
	public function getNameAttribute(): string{
		return $this->name;
	}
	/**
	 * @param int $userId
	 */
	public function setUserId(int $userId): void{
		$this->userId = $userId;
	}
	/**
	 * @return QMButton
	 */
	protected function getGetItHereButton(): QMButton{
		$button = self::getItHereButton($this->getItUrl, $this->name);
		//$button->setImage($this->getImage());
		//$button->setTextAndTitle("Get $this->displayName here");
		return $button;
	}
	/**
	 * @return QMCard
	 */
	public function getCard(){
		return $this->card ?: $this->setCard();
	}
	/**
	 * @return DataSourceQMCard
	 */
	public function setCard(): DataSourceQMCard{
		return $this->card = new DataSourceQMCard($this);
	}
	/**
	 * @return string
	 */
	public function getImage(): string{
		$image = $this->image;
		if(empty($image)){
			$image = $this->getQmUser()->getAvatar();
		}
		return $this->image = $image;
	}
	/**
	 * @return string
	 */
	public function getLongDescription(): string{
		return $this->longDescription;
	}
	/**
	 * @return string
	 */
	public function getShortDescription(): string{
		return $this->shortDescription;
	}
	/**
	 * @return QMUser
	 */
	public function getQmUser(): QMUser{
		// Don't create user property because there will be duplicate users and we can't use to store connections
		if($this->userId){
			$user = QMUser::find($this->getUserId());
		} else{
			$user = QMAuth::getQMUser();
		}
		return $user;
	}
	/**
	 * @return int|null
	 */
	public function getUserId(): ?int{
		if(!$this->userId && QMAuth::getQMUser()){
			$this->userId = QMAuth::getQMUser()->id;
		}
		return $this->userId;
	}
	/**
	 * @return array
	 */
	public function getOrGenerateSynonyms(): array{
		if(!$this->synonyms){
			return [];
		}
		return $this->synonyms;
	}
	/**
	 * @param string $message
	 */
	public function throwException(string $message){
		throw new LogicException($this->getLogMetaDataString() . $message);
	}
	/**
	 * @return $this|QMDataSource
	 */
	public function getPlatformAgnosticDataSource(): QMDataSource{
		if(strpos($this->getTitleAttribute(), "QuantiModo") !== false){
			return self::getQuantiModoDataSource();
		}
		return $this;
	}
	/**
	 * @param $arrayOrObject
	 */
	public function populateFieldsByArrayOrObject(array|object $arrayOrObject): void{
		if(!$arrayOrObject){
			return;
		}
		$obj = ObjectHelper::convertToObject($arrayOrObject);
		foreach($this as $propertyName => $currentValue){
			$providedValue = ObjectHelper::getPropertyValueSnakeInsensitive($obj, $propertyName);
			if($providedValue === null){
				continue;
			}
			$providedValue = StaticModel::jsonDecodeAndCastToIntIfNecessary($providedValue, $propertyName);
			$this->$propertyName = $providedValue;
		}
	}
	/**
	 * @param array|object $arrayOrObject
	 * @return static|null|bool
	 */
	public static function instantiateIfNecessary(array|object|string $arrayOrObject){
		if($arrayOrObject instanceof static){
			return $arrayOrObject;
		}
		$model = new static();
		$model->populateFieldsByArrayOrObject($arrayOrObject);
		return $model;
	}
	/**
	 * @param array|Collection $array
	 * @return array
	 */
	public static function instantiateArray($array): array{
		$models = [];
		foreach($array as $item){
			$models[] = static::instantiateIfNecessary($item);
		}
		return $models;
	}
	/**
	 * @param array $params
	 * @return QMDataSource[]
	 */
	public static function get(array $params = []): array{
		$i = QMSpreadsheetImporter::get([]);
		$c = QMConnector::get([]);
		$arr = array_merge($i, $c);
		return QMArr::filter($arr, $params);
	}
	/**
	 * @param string $sourceName
	 * @param int $userId
	 * @return QMSpreadsheetImporter
	 */
	public static function getByNameAndUserId(string $sourceName, int $userId): QMDataSource{
		$u = QMUser::find($userId);
		$sources = $u->getQMDataSources();
		if(!$sources){
			le("No sources!");
		}
		$importer = Arr::first($sources, static function($source) use ($sourceName){
			/** @var QMDataSource $source */
			return $source->name === $sourceName || $sourceName === $source->displayName;
		});
		if(!$importer){
			throw new RuntimeException("No data source matching $sourceName for $u");
		}
		return $importer;
	}
	/**
	 * @param string $name
	 * @return QMDataSource
	 */
	public static function getByName(string $name): QMDataSource{
		$sources = self::get();
		$importer = Arr::first($sources, static function($source) use ($name){
			/** @var QMDataSource $source */
			return $source->name === $name || $name === $source->displayName;
		});
		if(!$importer){
			throw new RuntimeException("No data source matching $name");
		}
		return $importer;
	}
	/**
	 * @param int $id
	 * @return string
	 * @throws NotFoundException
	 */
	public static function getNameById(int $id): string{
		$anonymous = QMConnector::getAnonymousConnectors();
		$match = Arr::first($anonymous, function($c) use ($id){
			/** @var QMConnector $c */
			return $c->getId() === $id;
		});
		if($match){
			$name = $match->name;
			return $name;
		}
		/** @var QMDataSource $match */
		$matches = static::get([self::FIELD_ID => $id]);
		if(!$matches){
			static::get([self::FIELD_ID => $id]);
			throw new NotFoundException("No data source with id $id");
		}
		return $matches[0]->name;
	}
	public function getUrl(array $params = []): string{
		return $this->getConnectWebPageUrl();
	}
	/**
	 * @return QMDataSource[]
	 */
	public static function getAll(): array{
		return self::getAnonymousDataSources();
	}
	/**
	 * @return string
	 */
	public function getResponsesFolder(): string{
		$ns = $this->getResponsesNameSpace();
		return FileHelper::namespaceToFolder($ns);
	}
	/**
	 * @return string
	 */
	private function getResponsesNameSpace(): string{
		$connectorClass = $this->getTitleAttribute();
		$connectorClass = QMStr::toClassName($connectorClass);
		return "App\DataSources\Connectors\Responses\\$connectorClass";
	}
	/**
	 * @param string $uriPath
	 * @param $response
	 * @return array
	 */
	public function generateStaticModel(string $uriPath, $response): array{
		if(is_array($response) && isset($response[0])){
			$response = $response[0];
		}
		$class = $this->uriPathToResponseClass($uriPath);
		if(!is_string($response)){
			$response = json_encode($response);
		}
		return CodeGenerator::jsonToBaseModel($class, $response);
	}
	/**
	 * @param string $uriPath
	 * @param $response
	 */
	public function saveStaticModelResponse(string $uriPath, $response){
		$this->generateStaticModel($uriPath, $response);
	}
	public function uriPathToResponseClass(string $uri): string{
		$shortClassName = $this->getResponseShortClassName($uri);
		$class = $this->getResponsesNameSpace() . "\\" . $shortClassName;
		return $class;
	}
	private function getResponseShortClassName(string $uri): string{
		$arr = explode('/', $uri);
		$keep = [];
		foreach($arr as $segment){
			if(stripos($segment, 'http') !== false || stripos($segment, '.') !== false ||
				stripos($segment, 'user') !== false || stripos($segment, '~') !== false ||
				stripos($segment, ':') !== false || stripos($segment, '-') !== false ||
				stripos($segment, 'date') !== false || stripos($segment, 'v1') === 0 ||
				//stripos($segment, 'accounts') === 0 ||
				is_numeric($segment) || empty($segment) || strtotime($segment)){
				continue;
			}
			$keep[] = QMStr::toClassName($segment);
		}
		$class = str_replace("Connector", "", (new \ReflectionClass(static::class))->getShortName());
		return QMStr::toClassName($class . implode("", $keep));
	}
	/**
	 * @param string $className
	 * @param $data
	 * @return mixed
	 */
	protected function instantiateResponseObject(string $className, $data){
		$ns = $this->getResponsesNameSpace();
		$fullClass = $ns . '\\' . $className;
		if(!class_exists($fullClass)){
			if(!EnvOverride::isLocal()){
				le("$fullClass doesn't exist so run this test locally and commit the file");
			}
			CodeGenerator::jsonToBaseModel($fullClass, $data);
			le("just generated code so have to rerun for autoload to occur.");
		}
		return new $fullClass($data);
	}
	public function getFontAwesome(): string{
		return $this->fontAwesome ?? Connector::FONT_AWESOME;
	}
	public function getSubtitleAttribute(): string{
		return $this->getLongDescription();
	}
	/**
	 * @param $nameOrId
	 * @return QMDataSource|null
	 */
	public static function findByNameIdOrSynonym($nameOrId){
		return self::getDataSourceByNameOrIdOrSynonym($nameOrId);
	}
}
