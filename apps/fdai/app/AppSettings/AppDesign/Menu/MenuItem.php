<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings\AppDesign\Menu;
use App\AppSettings\AppDesign\StateParams;
use App\AppSettings\AppSettings;
use App\Logging\QMLog;
use App\Slim\Model\States\IonicState;
use App\Slim\Model\StaticModel;
use App\Types\QMStr;
use App\Utils\UrlHelper;
class MenuItem extends StaticModel{
    public $stateName;
    public $href;
    public $url;
    public $icon;
    public $subMenu;
    public $params;
    public $title;
    public $id;
    public $showSubMenu;
    private $state;
    /**
     * @param MenuItem $menuItem
     */
    public function __construct($menuItem){
        $this->populateFieldsByArrayOrObject($menuItem);
        $this->processHref();
        $this->getHref();
        $this->generateId();
        if($this->subMenu){
            $this->subMenu = MenuSettings::processSingleMenu($this->subMenu);
        }
        $this->setParams();
        if($this->subMenu){
            $this->href = null;
        }
    }
    /**
     * @return string
     */
    private function getVariableCategoryName(): ?string {
        if(!$this->getParams()){
            return null;
        }
        return $this->params->variableCategoryName ?? null;
    }
    /**
     * @return string
     */
    private function getVariableName(): ?string {
        if(!$this->getParams()){
            return null;
        }
        return $this->params->variableName ?? null;
    }
    private function processHref(){
        if(!isset($this->href)){
            return;
        }
        $variableCategoryName = UrlHelper::getParam("variableCategoryName", $this->href);
        if($variableCategoryName){
            $this->href = str_replace("?variableCategoryName=", '-category/', $this->href);
        }
        if(strpos($this->href, 'history-all') !== false && strpos($this->href, 'history-all-category') === false){
            $this->href .= "-category/Anything";
        }
        $this->href = str_replace("-category/:variableCategoryName-category/", '-category/', $this->href);
        //if(strpos($this->href, "variableCategoryName") !== false){throw new \LogicException("menuItem->href Should not contain variableCategoryName!");}
        //$this->href = str_replace("?", "", $this->href);
        $this->href = self::replaceDeprecatedStuff($this->href);
        $this->setVariableCategoryNameStateParamIfInHref();
        $this->appendCategoryToStateNameIfInHref();
    }
    /**
     * @return bool
     */
    private function defaultStateParamsHaveVariableCategory(){
        if($this->subMenu){
            return false;
        }
        if(!$this->stateName){
            return false;
        }
        $state = $this->getState();
        if(!$state){
            return false;
        }
        $defaultParams = $this->getState()->getParams();
        return $defaultParams->hasVariableCategoryProperty();
    }
    private function setVariableCategoryNameStateParamIfInHref(){
        if(!isset($this->href)){
            return;
        }
        if(!$this->defaultStateParamsHaveVariableCategory()){
            return;
        }
        /** @noinspection MissingIssetImplementationInspection */
        if($this->getParams() && isset($this->getParams()->variableCategoryName)){
            return;
        }
        $subString = '-category/';
        $href = $this->href;
        if(strpos($href, $subString)){
            $variableCategoryName = QMStr::after($subString, $href);
            $variableCategoryName = QMStr::before($subString, $variableCategoryName, $variableCategoryName);
            $variableCategoryName = QMStr::before('?', $variableCategoryName, $variableCategoryName);
            $variableCategoryName = rawurldecode($variableCategoryName);
            $this->getParams()->setVariableCategoryName($variableCategoryName);
        }
    }
    /**
     * @param $href
     * @return mixed
     */
    private static function replaceDeprecatedStuff($href){
        // Old => New
        $deprecatedStuff = [
            '/app/reminders-list/' => '/app/variable-list?variableCategoryName='
        ];
        foreach($deprecatedStuff as $old => $new){
            $href = str_replace($old, $new, $href);
        }
        return $href;
    }
    /**
     * @return string
     */
    public function generateId(): string{
        $name = $this->getStateName(true);
        if(!$name){
            $name = $this->getTitleAttribute();
        }  // A parent menu
        $name = QMStr::snakize($name);
        $id = QMStr::slugify($name);
        $category = $this->getVariableCategoryName();
        if($category){
            $id .= '-'.QMStr::slugify($category);
        }
        $variable = $this->getVariableName();
        if($variable){
            $id .= '-'.QMStr::slugify($variable);
        }
        return parent::setId($id);
    }
    public function appendCategoryToStateNameIfInHref(){
        if(!isset($this->href)){
            return;
        }
        if(!$this->defaultStateParamsHaveVariableCategory()){
            return;
        }
        if($this->stateName &&
            (strpos($this->href, '-category/') !== false) &&
            strpos($this->stateName, 'Category') === false &&
            IonicState::getByName($this->stateName .= 'Category')){
            $this->stateName .= 'Category';
        }
    }
    /**
     * @param AppSettings $appSettings
     */
    public static function checkMenuItems($appSettings){
        if(!isset($appSettings->appDesign->menu)){
            QMLog::error("No appSettings->appDesign->menu", $appSettings);
            return;
        }
        $appDesign = $appSettings->appDesign;
        /** @var MenuSettings $menuSettings */
        $menuSettings = $appDesign->menu;
        if(!isset($menuSettings->active)){
            QMLog::error("No appSettings->appDesign->menu->active for " . $appSettings->getClientId());
            return;
        }
        /** @var MenuItem[] $menuItems */
        $menuItems = $menuSettings->active;
        foreach($menuItems as $menuItem){
            if(!$menuItem->icon){le("No icon!");}
            if($menuItem->showSubMenu){$menuItem->showSubMenu = false;}  // For some reason Discoveries is always expanded
            if($menuItem->subMenu){
                foreach($menuItem->subMenu as $subMenuItem){
                    if(!$subMenuItem->icon){
                        le("No icon!");
                    }
                    if(!$subMenuItem->href){
                        le("No href in subMenuItem: ".json_encode($subMenuItem));
                    }
                }
            }else if(!$menuItem->href){
                le("No href!");
            }
        }
    }
    /**
     * @param string $href
     */
    public function setHref($href): void{
        $this->href = $href;
    }
    /**
     * @param bool $withoutApp
     * @return string
     */
    public function getStateName(bool $withoutApp = false){
        if($this->subMenu){
            return false;
        }
        if($this->stateName){
            $this->stateName = str_replace('CategoryCategory', '', $this->stateName);
            $stateName = $this->stateName;
        }elseif($menuHref = $this->href){
            $state = IonicState::getByHref($menuHref);
            if(!$state){
                $this->invalidState();
            }
            $stateName = $state->getNameAttribute();
        }elseif($menuUrl = $this->url){
            $state = IonicState::getByUrl($menuUrl);
            if(!$state){
                $this->invalidState();
            }
            $stateName = $state->getNameAttribute();
        }else{
            $this->invalidState();
        }
        if(!isset($stateName)){
            return false;
        }
        if($withoutApp){
            return str_replace("app.", "", $stateName);
        }
        if(!IonicState::getByName($stateName)){
            if(stripos($stateName, 'Category') !== false){
                $stateName = str_replace('Category', '', $stateName);
            }
            /** @noinspection NotOptimalIfConditionsInspection */
            if(!IonicState::getByName($stateName)){
                $this->logError("$stateName not found!");
            }
        }
        return $this->stateName = $stateName;
    }
    /**
     * @return array
     */
    private function getUrlParamsForHref(): array {
        $state = $this->getState();
        $defaultParams = $state->getParams();
        $menuParams = $this->getParams();
        $urlParams = [];
        foreach($menuParams as $key => $value){
            if(!property_exists($defaultParams, $key)){
                //$urlParams[$key] = $value;
                unset($menuParams->$key);
                if($value){
                    $this->logError("Param $key not in default params for state $state->name but menu value is $value");
                }
                continue;
            }
            if($value !== $defaultParams->$key){
                $urlParams[$key] = $value;
            }
        }
        return $urlParams;
    }
    /**
     * @return string
     */
    public function getHref(): ?string {
        if($this->subMenu){return null;}
        $url = $this->getUrl();
        if(!$url){return $this->href;}
        $params = $this->getParams();
        if(strpos($url, ':')){
            $propertyName = QMStr::after(":", $url);
            $url = str_replace(":$propertyName", urlencode($params->$propertyName), $url);
        }
        $href = '#/app'.$url;
        $category = $this->getVariableCategoryName();
        $hrefDoesNotContainCategoryString = stripos($href, "-category") === false;
        if($category && $hrefDoesNotContainCategoryString && stripos($href, $category) === false){
            $href .= "-category/".urlencode($category);
            //unset($this->params->variableCategoryName);
        }
        if($urlParams = $this->getUrlParamsForHref()){
            $href = UrlHelper::addParams($href, $urlParams);
        }
        $this->setVariableCategoryNameStateParamIfInHref();
        $this->appendCategoryToStateNameIfInHref();
        return $this->href = $href;
    }
    private function invalidState(){
        $this->invalid("Please select a state in the menu item with title $this->title");
    }
    /**
     * @param array $params
     * @return string
     */
    public function getUrl(array $params = []): string{
        if($this->subMenu){
            return "";
        }
        if($this->url){
            return $this->url;
        }
        if($this->stateName){
            $menuStateName = $this->stateName = str_replace('CategoryCategory', '', $this->stateName);
            $state = IonicState::getByName($menuStateName);
        }else if($menuHref = $this->href){
            $state = IonicState::getByHref($menuHref);
        }else{
            $this->invalidState();
        }
        if(!isset($state)){
            return "";
        }
        return $this->url = $state->getUrl();
    }
    /**
     * @param MenuItem $menuItem
     */
    public static function checkMenuItem($menuItem){
        if(isset($menuItem->subMenu)){
            foreach($menuItem->subMenu as $subMenuItem){
                self::checkMenuItem($subMenuItem);
            }
        }
        if(isset($menuItem->href)){
            if(stripos($menuItem->href, "-app-") !== false){
                le("-app- in $menuItem->href");
            }
            if((strpos($menuItem->href, "history-all") !== false) && stripos($menuItem->href, "-category") === false){
                le("No -category in $menuItem->href");
            }
        }
    }
    /**
     * @return StateParams
     */
    public function getParams(){
        $params = $this->params;
        if(!$params){
            $params = new StateParams();
        }else{
            $params = StateParams::instantiateIfNecessary($params);
        }
        return $this->params = $params;
    }
    /**
     * @return string
     */
    public function getTitleAttribute(): string{
        return $this->title;
    }
    /**
     * @return string
     */
    public function getId(){
        return $this->id;
    }
    /**
     * @return bool
     */
    public function getShowSubMenu(){
        return $this->showSubMenu;
    }
    /**
     * @return IonicState|bool
     */
    public function getState(): ?IonicState {
        if($this->subMenu){
            if($this->stateName){
                $this->logInfo("Why does menu have $this->stateName and a submenu? Deleting state name");
            }
            $this->state = $this->stateName = null;
        }
        return $this->state ?: $this->setState();
    }
    /**
     * @return string
     */
    public function getLogMetaDataString(): string {
        return $this->title;
    }
    /**
     * @return IonicState
     */
    public function setState(): ?IonicState {
        $name = $this->getStateName();
        if(!$name){
            return null;
        }
        $state = IonicState::getByName($this->getStateName());
        return $this->state = $state;
    }
    /**
     * @return object
     */
    public function setParams(){
        $state = $this->getState();
        if(!$state){
            return $this->params;
        }
        $stateParams = $state->getParams();
        if(!isset($this->params)){
            return $this->params = $stateParams;
        }
        $menuParams = $this->getParams();
        $menuParams->addAndPopulateExtraFieldsByArrayOrObject($stateParams);
        if($this->icon){
            $menuParams->setIonIcon($this->icon);
        }
        return $this->params;
    }
}
