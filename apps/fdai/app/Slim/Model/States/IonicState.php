<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\States;
use App\AppSettings\AppDesign\StateParams;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Models\WpLink;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\User\UserIdProperty;
use App\Traits\HardCodable;
use App\Types\ObjectHelper;
use App\Types\QMArr;
use App\Types\QMStr;
use App\UI\IonIcon;
use App\Utils\IonicHelper;
class IonicState {
	use HardCodable;
	public const UNIQUE_INDEX_COLUMNS = [
		'name',
	];
	public const asNeededMeds = 'app.asNeededMeds';
	public const charts = 'app.charts';
	public const chartSearch = 'app.chartSearch';
	public const configuration = 'app.configuration';
	public const configurationClientId = 'app.configurationClientId';
	public const contact = 'app.contact';
	public const dataSharing = 'app.dataSharing';
	public const favoriteAdd = 'app.favoriteAdd';
	public const favorites = 'app.favorites';
	public const favoriteSearch = 'app.favoriteSearch';
	public const feedback = 'app.feedback';
	public const help = 'app.help';
	public const history = 'app.history';
	public const historyAll = 'app.historyAll';
	public const historyAllCategory = 'app.historyAllCategory';
	public const historyAllVariable = 'app.historyAllVariable';
	public const import = 'app.import';
	public const importNative = 'app.importNative';
	public const intro = 'app.intro';
	public const login = 'app.login';
	public const manageScheduledMeds = 'app.manageScheduledMeds';
	public const map = 'app.map';
	public const measurementAdd = 'app.measurementAdd';
	public const measurementAddSearch = 'app.measurementAddSearch';
	public const measurementAddVariable = 'app.measurementAddVariable';
	public const notificationPreferences = 'app.notificationPreferences';
	public const onboarding = 'app.onboarding';
	public const outcomesAll = 'app.outcomesAll';
	public const outcomeSearch = 'app.outcomeSearch';
	public const predictorsAggregated = 'app.predictorsAggregated';
	public const predictorsAll = 'app.predictorsAll';
	public const predictorSearch = 'app.predictorSearch';
	public const predictorsNegative = 'app.predictorsNegative';
	public const predictorsNegativeVariable = 'app.predictorsNegativeVariable';
	public const predictorsPositive = 'app.predictorsPositive';
	public const predictorsPositiveVariable = 'app.predictorsPositiveVariable';
	public const predictorsUser = 'app.predictorsUser';
	public const reminderAdd = 'app.reminderAdd';
	public const reminderSearch = 'app.reminderSearch';
	public const remindersInbox = 'app.remindersInbox';
	public const remindersInboxCompact = 'app.remindersInboxCompact';
	public const remindersInboxToday = 'app.remindersInboxToday';
	public const remindersList = 'app.remindersList';
	public const remindersListCategory = 'app.remindersListCategory';
	public const remindersManage = 'app.remindersManage';
	public const remindersManageCategory = 'app.remindersManageCategory';
	public const searchVariablesWithCommonPredictors = 'app.searchVariablesWithCommonPredictors';
	public const searchVariablesWithUserPredictors = 'app.searchVariablesWithUserPredictors';
	public const settings = 'app.settings';
	public const studies = 'app.studies';
	public const studiesCreated = 'app.studiesCreated';
	public const studiesOpen = 'app.studiesOpen';
	public const study = 'app.study';
	public const studyCreation = 'app.studyCreation';
	public const studyJoin = 'app.studyJoin';
	public const tabs = 'app.tabs';
	public const tagAdd = 'app.tagAdd';
	public const tageeSearch = 'app.tageeSearch';
	public const tagSearch = 'app.tagSearch';
	public const todayMedSchedule = 'app.todayMedSchedule';
	public const track = 'app.track';
	public const upgrade = 'app.upgrade';
	public const variableList = 'app.variableList';
	public const variableListCategory = 'app.variableListCategory';
	public const variableSettings = 'app.variableSettings';
	public const variableSettingsVariableName = 'app.variableSettingsVariableName';
	public const welcome = 'app.welcome';
	protected static $byHref = [];
	private static $states;
	private static $statesByName;
	public $ionIcon;
	public $name;
	public $title;
	public $url;
	public $visible;
	public $image;
	public $description;
	public $params;
	protected $wpLink;
	public function __construct($obj){
		foreach($obj as $key => $value){
			$this->$key = $value;
		}
		foreach($obj->params as $key => $value){
			$this->$key = $value;
		}
	}
	/**
	 * @return IonicState[]
	 */
	public static function getStates(): array{
		if(self::$states){
			return self::$states;
		}
		$stdClassObjects = self::getStatesJson();
		$states = [];
		foreach($stdClassObjects as $item){
			if(empty($item->name) || $item->name === "app"){
				continue;
			}
			if(empty($item->params->ionIcon)){
				continue;
			}
			$states[] = new self($item);
		}
		return self::$states = $states;
	}
	/**
	 * @param array $requestParams
	 * @return IonicState[]
	 */
	public static function get(array $requestParams = []): array{
		return self::getStates();
	}
	/**
	 * @return array
	 */
	public static function getStatesJson(): array{
		$stdClassObjects = ObjectHelper::getJsonFileAsArrayOfObjects(FileHelper::projectRoot() . '/data/states.json');
		QMArr::sortAscending($stdClassObjects, 'name');
		return $stdClassObjects;
	}
	/**
	 * @return array
	 */
	public static function getStateNamesMap(): array{
		$states = self::getStatesJson();
		$namesMap = [];
		foreach($states as $state){
			$key = str_replace('app.', '', $state->name);
			if(empty($key)){
				continue;
			}
			if($key === "app"){
				continue;
			}
			$namesMap[$key] = $state->name;
		}
		return $namesMap;
	}
	/**
	 * @param $name
	 * @return bool|IonicState
	 */
	public static function getByName(string $name){
		if(isset(self::$statesByName[$name])){
			return self::$statesByName[$name];
		}
		if(stripos($name, 'app.') === false){
			$name = 'app.' . $name;
		}
		$states = self::getStates();
		foreach($states as $state){
			$nameToCheck = $state->getNameAttribute();
			if(!$nameToCheck){
				continue;
			}
			if(QMStr::isCaseInsensitiveMatch($nameToCheck, $name)){
				self::$statesByName[$name] = $state;
				return $state;
			}
		}
		return self::$statesByName[$name] = false;
	}
	/**
	 * @return string
	 */
	public function getNameAttribute(): string{
		return $this->name;
	}
	/**
	 * @param array $params
	 * @return string
	 */
	public function getUrl(array $params = []): string{
		return $this->url;
	}
	/**
	 * @return StateParams
	 */
	public function getParams(): StateParams{
		$params = $this->params;
		if(!$params){
			$params = new StateParams();
		} else{
			$params = StateParams::instantiateIfNecessary($params);
		}
		return $this->params = $params;
	}
	/**
	 * @param object $params
	 */
	public function setParams($params){
		$this->params = $params;
	}
	/**
	 * @param string $menuUrl
	 * @return IonicState
	 */
	public static function getByUrl(string $menuUrl): ?IonicState{
		$menuUrl = str_replace('/app', '', $menuUrl);
		$states = self::getStates();
		foreach($states as $state){
			$stateUrl = $state->getUrl();
			if($stateUrl === $menuUrl){
				return $state;
			}
		}
		return null;
	}
	/**
	 * @param string $href
	 * @return IonicState
	 */
	public static function getByHref(string $href): ?IonicState{
		if(isset(static::$byHref[$href])){
			return static::$byHref[$href];
		}
		$menuUrl = str_replace('#/app', '', $href);
		$states = self::getStates();
		$matches = [];
		foreach($states as $state){
			//$stateUrl = StringHelper::getStringBeforeSubString(':/', $state->getUrl());
			$stateUrl = $state->getUrl();
			if($stateUrl === $menuUrl){
				return static::$byHref[$href] = $state;
			}
			if(stripos($menuUrl, $stateUrl) === 0){
				$matches[] = $state;
			}
		}
		if(count($matches) === 1){
			return $matches[0];
		}
		if(count($matches) > 1){
			$longest = 0;
			$bestMatch = null;
			/** @var IonicState $match */
			foreach($matches as $match){
				$length = strlen($match->url);
				if($length > $longest){
					$longest = $length;
					$bestMatch = $match;
				}
			}
			return static::$byHref[$href] = $bestMatch;
		}
		le("Could not get state by $href");
		throw new \LogicException();
	}
	/**
	 * @return WpLink
	 */
	public function getWpLink(): WpLink{
		if($this->wpLink){
			return $this->wpLink;
		}
		$title = $this->getTitleAttribute();
		$params = [
			WpLink::FIELD_CLIENT_ID => BaseClientIdProperty::CLIENT_ID_SYSTEM,
			WpLink::FIELD_LINK_DESCRIPTION => $this->description,
			WpLink::FIELD_LINK_IMAGE => IonIcon::getIonIconPngUrl($this->getIonIcon()),
			WpLink::FIELD_LINK_NAME => $title,
			WpLink::FIELD_LINK_NOTES => json_encode($this),
			WpLink::FIELD_LINK_OWNER => UserIdProperty::USER_ID_SYSTEM,
			WpLink::FIELD_LINK_TARGET => "self",
			WpLink::FIELD_LINK_URL => $this->getProductionUrl(),
			WpLink::FIELD_LINK_VISIBLE => $this->getVisibility(),
		];
		try {
			return $this->wpLink = WpLink::firstOrCreate($params);
		} catch (\Throwable $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
			return $this->wpLink = WpLink::firstOrCreate($params);
		}
	}
	/**
	 * @return mixed
	 */
	public function getIonIcon(): string{
		return $this->ionIcon;
	}
	/**
	 * @return mixed
	 */
	public function getTitleAttribute(): string{
		return $this->title;
	}
	private function getVisibility(): bool{
		if($this->visible !== null){
			return $this->visible;
		}
		return true;
	}
	/**
	 * @return mixed
	 */
	public function getImage(): string{
		if(!$this->image){
			$this->image = IonIcon::getIonIconPngUrl($this->getIonIcon());
		}
		return $this->image;
	}
	public function getProductionUrl(): string{
		return IonicHelper::getIonicAppUrl(null, $this->url);
	}
	/**
	 * @inheritDoc
	 */
	public static function getHardCodedDirectory(): string{
		return "app/States";
	}
	protected function generateFileContentOfHardCodedModel(): string{
		$namespace = QMStr::pathToNameSpace($this->getHardCodedDirectory());
		$shortClassName = $this->getHardCodedShortClassName();
		$use = $this->getUseStatements();
		$properties = $this->getHardCodedPropertiesString();
		return "<?php
namespace $namespace;
$use
class $shortClassName extends " . QMStr::toShortClassName(static::class) . " {
$properties
}";
	}
	protected function getHardCodedShortClassName(): string{
		return QMStr::toShortClassName($this->name);
	}
}
