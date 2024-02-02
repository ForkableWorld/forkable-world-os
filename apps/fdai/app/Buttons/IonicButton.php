<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons;
use App\Files\FileFinder;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Model\States\IonicState;
use App\Slim\View\Request\QMRequest;
use App\Types\QMStr;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\UI\IonIcon;
use App\Utils\AppMode;
use App\Utils\IonicHelper;
use App\Utils\UrlHelper;
class IonicButton extends QMButton {
	public const IONIC_WILDCARD_HOST = 'quantimodo.com';
	public $stateName;
	public $stateParams;
	public $title = "Start Tracking";
	public $ionIcon = IonIcon::ION_ICON_INBOX;
	public $link = "";
	const BUTTONS_FOLDER = parent::BUTTONS_FOLDER . '/States';
	public function __construct(IonicState $state = null){
		if($state){
			parent::__construct($state->getTitleAttribute(), null, null, $state->getIonIcon());
			$this->populateByStateName($state->getNameAttribute());
		} else{
			parent::__construct();
			$origin = IonicHelper::ionicOrigin();
			$link = $this->link;
			$url = $origin.$link;
			$this->link = UrlHelper::addParams($url, $this->getParameters());
		}
		//$this->link = IonicHelper::addStagingApiUrlIfNecessary($this->link);
	}
	/**
	 * @param null $params
	 * @return QMButton
	 */
	public static function instance($params = null): QMButton{
		$i = new static();
		if($params){
			$i->setParameters($params);
		}
		return $i;
	}
	public function setUrl(string $url, array $params = [], bool $allowPaths = false): QMButton{
		return parent::setUrl($url, $params, $allowPaths);
	}
	public static function generateButtons(){
		$states = IonicState::getStates();
		foreach($states as $state){
			if(stripos($state->url, '/:')){
				if(stripos($state->url, 'variableName')){
					$b = new VariableDependentStateButton();
				} elseif(stripos($state->url, 'variableCategoryName')){
					$b = new VariableCategoryStateButton();
				} else{
					QMLog::info("Skipping $state->url button");
					continue;
				}
			} else{
				$b = new IonicButton($state);
			}
			$b->populateByStateName($state->getNameAttribute());
			$b->fontAwesome = FontAwesome::findIconLike($b->getTitleAttribute());
			$b->setImage(ImageUrls::findImageLike($b->getTitleAttribute()));
			$b->saveHardCodedModel();
			if(!$b->fontAwesome){
				le("No fontAwesome for $b");
			}
		}
	}
	public static function generateIonicStateButtons(){
		self::generateButtons();
	}
	public static function getStateButtons(): array{
		return IonicButton::all();
	}
	protected function getHardCodedShortClassName(): string{
		$class = QMStr::toClassName($this->getId());
		return $class;
	}
	/**
	 * @inheritDoc
	 */
	public static function getHardCodedDirectory(): string{
		$folder = static::BUTTONS_FOLDER;
		return $folder;
	}
	public function setParameters(array $parameters){
		parent::setParameters($parameters);
		$this->stateParams = $parameters;
	}
	public static function addFontAwesomeProperties(){
		$me = new static();
		$dir = $me->getHardCodedDirectory();
		$files = FileFinder::listFiles($dir);
		$classes = FileHelper::getClassesInFolder($dir);
		foreach($classes as $class){
			try {
				/** @var QMButton $model */
				$model = new $class();
				$contents = $model->getHardCodedFileContents();
			} catch (\Throwable $e) {
				QMLog::info(__METHOD__.": ".$e->getMessage());
			}
		}
	}
	/**
	 * @param bool $includeSubFolders
	 * @return IonicButton[]
	 */
	public static function exceptVariableDependent(): array{
		$all = static::all();
		$notDependent = [];
		foreach($all as $button){
			if($button instanceof VariableDependentStateButton){
				continue;
			}
			$notDependent[$button->getTitleAttribute()] = $button;
		}
		return $notDependent;
	}
	public static function generateDevUrl(array $params = []): string{
		$url = static::url($params);
		return DevIonicButton::toDevIonicUrl($url);
	}
	/**
	 * @param string $stateName
	 * @param array $params
	 */
	protected function populateByStateName(string $stateName, array $params = []){
		if(stripos($stateName, 'app.') === false){
			$stateName = 'app.' . $stateName;
		}
		$this->stateName = $stateName;
		$id = str_replace('app.', '', $stateName);
		$id = QMStr::camelToSlug($id);
		$this->setId($id . '-state-button');
		$this->stateParams = $params;
		$this->setUrl(IonicHelper::getIonicAppUrlForState($stateName, $params));
		$state = IonicState::getByName($stateName);
		if($state->ionIcon){
			$this->setIonIconAndImage($state->ionIcon);
		}
		if(!$state->title){
			le("");
		}
		if(!$this->title){
			$this->setTextAndTitle($state->title);
		}
		if(!$this->ionIcon){
			$this->setIonIcon($state->ionIcon);
		}
	}
	/**
	 * @param string $key
	 * @param $value
	 */
	public function setParam(string $key, $value){
		$this->stateParams[$key] = $value;
		parent::setParam($key, $value);
	}
	public function getUrl(array $params = []): string{
		$url = parent::getUrl($params);
		if(!str_starts_with($url, "http")){
			$url = IonicHelper::getIonicAppUrl(null, $url, $params);
		}
		return $url;
	}
}
