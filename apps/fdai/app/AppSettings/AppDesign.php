<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings;
use App\AppSettings\AppDesign\AliasSettings;
use App\AppSettings\AppDesign\DefaultTrackingReminderSettings;
use App\AppSettings\AppDesign\FeaturesListSettings;
use App\AppSettings\AppDesign\IntroSettings;
use App\AppSettings\AppDesign\Menu\MenuSettings;
use App\AppSettings\AppDesign\OnboardingSettings;
use App\Exceptions\ExceptionHandler;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\VariableSearchResult;
use Exception;
use Throwable;
class AppDesign {
    public $aliases;
    public $floatingActionButton;
    public $helpCard;
    public $intro;
    public $menu;
    public $onboarding;
    public $upgradePleadingCard;
    public $featuresList;
    public $defaultState;
    public $defaultTrackingReminderSettings;
    public $primaryOutcomeVariable;
    /**
     * AppDesign constructor.
     * @param AppSettings|object $appSettings
     */
    public function __construct($appSettings = null){
        if(!isset($appSettings->appDesign) || $appSettings->appDesign == "null" || $appSettings->appDesign == ""){
            $appSettings->appDesign = null;
        }
        if(is_string($appSettings->appDesign)){
            $decoded = json_decode($appSettings->appDesign, false);
            if(!$decoded){QMLog::exceptionIfTesting("Could not decode $appSettings->clientId app design: ".$appSettings->appDesign);}
            $appSettings->appDesign = $decoded;
        }
        if(!$appSettings->appDesign){$appSettings->appDesign = $this;}
        $appType = !empty($appSettings->appType) ? $appSettings->appType : 'general';
        try {
            $defaultAppDesign = self::getDefaultAppDesign($appType);
            foreach($defaultAppDesign as $key => $value){
                $this->$key = $appSettings->appDesign->$key ?? $value;
            }
        } catch (Exception $e) {
            ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
        }
        $this->setMenu($appSettings);
        $this->upgradePleadingCard = null;
        $this->setIntro($appSettings);
        $this->setOnboarding($appSettings);
        $this->setAliases($appSettings);
        $this->setFeaturesList($appSettings);
        $this->setDefaultTrackingReminderSettings($appSettings);
    }
    /**
     * @param null $appType
     * @return bool|string[]
     */
    public static function getDefaultAppDesign($appType = null){
        if(!$appType || $appType === 'custom'){
            $appType = 'general';
        }
        $pathToConfig = FileHelper::projectRoot().'/data/default-configs/'.$appType.'.app-design.json';
        try {
            $contents = file_get_contents($pathToConfig);
        } catch (Throwable $e){
            ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
            $appType = 'general';
            $pathToConfig = FileHelper::projectRoot().'/data/default-configs/'.$appType.'.app-design.json';
            $contents = file_get_contents($pathToConfig);
        }
        return json_decode($contents);
    }
    /**
     * @param $array
     * @param $deprecatedProperties
     */
    public static function removeDeprecatedPropertiesFromObjectsInArray($array, $deprecatedProperties){
        foreach($array as $object){
            self::removeDeprecatedPropertiesFromObject($object, $deprecatedProperties);
        }
    }
    /**
     * @param $object
     * @param $deprecatedProperties
     */
    private static function removeDeprecatedPropertiesFromObject($object, $deprecatedProperties){
        foreach($deprecatedProperties as $deprecatedProperty){
            unset($object->$deprecatedProperty);
        }
    }
    public function removeCustomAppDesignProperties(){
        $allProperties = get_object_vars($this);
        foreach($allProperties as $key => $value){
            try {
                if(isset($this->$key)){
                    unset($this->$key->type);
                    unset($this->$key->custom);
                }
            } catch (Exception $e) {
	            QMLog::error($e->getMessage(), ['exception' => $e]);
            }
        }
    }
    /**
     * @param $appDisplayName
     * @param $property
     * @return mixed
     */
    private static function replaceAppDisplayName($appDisplayName, $property){
        $property = json_encode($property);
        $property = str_replace("QuantiModo", $appDisplayName, $property);
        return json_decode($property);
    }
	/**
	 * @param array|null $array $array
	 * @return array
	 */
    public static function removeNullItemsFromArray(?array $array): ?array{
        if(!$array){
            return $array;
        }
        $withoutNulls = [];
        foreach($array as $item){
            if($item){
                $withoutNulls[] = $item;
            }
        }
        return $withoutNulls;
    }
    /**
     * @return IntroSettings
     */
    public function getIntro(): IntroSettings{
        $intro = $this->intro;
        return $this->intro = IntroSettings::instantiateIfNecessary($intro);
    }
    /**
     * @return MenuSettings
     */
    public function getMenu(): MenuSettings{
        return $this->menu;
    }
    /**
     * @param AppSettings $appSettings
     */
    public function setMenu($appSettings){
        $this->menu = new MenuSettings($appSettings);
    }
    /**
     * @return AliasSettings
     */
    public function getAliases(): AliasSettings{
        return $this->aliases;
    }
    /**
     * @param AppSettings $appSettings
     */
    public function setAliases($appSettings){
        $this->aliases = new AliasSettings($appSettings);
    }
    /**
     * @param AppSettings $appSettings
     */
    public function setFeaturesList($appSettings){
        $this->featuresList = new FeaturesListSettings($appSettings);
    }
    /**
     * @return OnboardingSettings
     */
    public function getOnboarding(): OnboardingSettings{
        $onboarding = $this->onboarding;
        return $this->onboarding = OnboardingSettings::instantiateIfNecessary($onboarding);
    }
    /**
     * @param AppSettings $appSettings
     */
    public function setOnboarding($appSettings): void{
        $this->onboarding = new OnboardingSettings($appSettings);
        $this->onboarding = self::replaceAppDisplayName($appSettings->appDisplayName, $this->onboarding);
    }
    /**
     * @param AppSettings $appSettings
     */
    public function setIntro($appSettings): void{
        $this->intro = new IntroSettings($appSettings);
        $this->intro = self::replaceAppDisplayName($appSettings->appDisplayName, $this->intro);
    }
    /**
     * @param AppSettings $appSettings
     */
    public function setDefaultTrackingReminderSettings($appSettings): void{
        $this->defaultTrackingReminderSettings = new DefaultTrackingReminderSettings($appSettings);
    }

    /**
     * @return string
     */
    public function getDefaultState(): string {
        return $this->defaultState;
    }
    /**
     * @param string $defaultState
     */
    public function setDefaultState(string $defaultState): void{
        $this->defaultState = $defaultState;
    }
    /**
     * @return DefaultTrackingReminderSettings
     */
    public function getDefaultTrackingReminderSettings(): DefaultTrackingReminderSettings{
        return $this->defaultTrackingReminderSettings;
    }
    /**
     * @return VariableSearchResult
     */
    public function getPrimaryOutcomeVariable(): VariableSearchResult {
        $v = $this->primaryOutcomeVariable;
        if(!$v){$v = OverallMoodCommonVariable::toSearchResult();}
        return $this->primaryOutcomeVariable = $v;
    }
}
