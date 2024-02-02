<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings\AdditionalSettings;
use LogicException;
use App\Utils\AppMode;
use App\AppSettings\AppSettings;
use App\InputFields\InputField;
use App\Logging\QMLog;
use stdClass;

/** Class MonetizationSettings
 * @package App\AppSettings\AdditionalSettings
 */
class MonetizationSettings {
    public $subscriptionsEnabled;
    public $advertisingEnabled;
    public $playPublicLicenseKey;
    public $monetizationSettingsInstructionLinks;
    public $iosMonthlySubscriptionCode;
    public $iosYearlySubscriptionCode;
    public $hideBuyNowButtons;
    private $appSettings;
    /**
     * @param AppSettings $appSettings
     */
    public function __construct($appSettings = null){
        $this->appSettings = $appSettings;
        $this->setAdvertisingEnabledField();
        $this->setIosMonthlySubscriptionCode();
        $this->setIosYearlySubscriptionCode();
        $this->setPlayPublicLicenseKey();
        $this->setSubscriptionsEnabled();
        $this->setHideBuyNowButtons();
    }
    /**
     * @param AppSettings $appSettings
     */
    public static function checkPlayPublicLicenseKey($appSettings): void{
        if(!$appSettings->appStatus->buildEnabled->androidRelease){
            return;
        }
        if(!$appSettings->additionalSettings->monetizationSettings->playPublicLicenseKey){
            QMLog::info("No playPublicLicenseKey", ['buildSettings' => $appSettings->additionalSettings->buildSettings]);
        }
    }
    public function setSubscriptionsEnabled(): void{
        if(is_object($this->getStoredMonetizationSettingsField('subscriptionsEnabled'))){
            $this->subscriptionsEnabled = $this->getStoredMonetizationSettings()->subscriptionsEnabled;
        }
        if(!$this->subscriptionsEnabled){
            // TODO: Remove after all clients are updated
            $value = (!is_object($this->getStoredMonetizationSettingsField('subscriptionsEnabled'))) ? $this->getStoredMonetizationSettingsField('subscriptionsEnabled') : false;
            $this->subscriptionsEnabled = new InputField("Subscriptions Enabled", 'subscriptionsEnabled', $value, "bool", "Would you like to enable subscriptions adds and earn $2 per monthly subscriber?", true, null, null);
        }
    }
    public function setAdvertisingEnabledField(): void{
        if(is_object($this->getStoredMonetizationSettingsField('advertisingEnabled'))){
            $this->advertisingEnabled = $this->getStoredMonetizationSettings()->advertisingEnabled;
        }
        if(!$this->advertisingEnabled){
            // TODO: Remove after all clients are updated
            $value = (!is_object($this->getStoredMonetizationSettingsField('advertisingEnabled'))) ? $this->getStoredMonetizationSettingsField('advertisingEnabled') : false;
            $this->advertisingEnabled = new InputField("Advertising Enabled", 'advertisingEnabled', $value, "bool", "Would you like to show ads and earn 50% of all click revenue?", true, null, null);
        }
    }
    public function setPlayPublicLicenseKey(): void{
        if(is_object($this->getStoredMonetizationSettingsField('playPublicLicenseKey'))){
            $this->playPublicLicenseKey = $this->getStoredMonetizationSettings()->playPublicLicenseKey;
        }
        if(!$this->playPublicLicenseKey){
            // TODO: Remove after all clients are updated
            $value = (!is_object($this->getStoredMonetizationSettingsField('playPublicLicenseKey'))) ? $this->getStoredMonetizationSettingsField('playPublicLicenseKey') : null;
            $this->playPublicLicenseKey = new InputField("Play Public License Key", 'playPublicLicenseKey', $value, "string", "Can be found at the <a href=\"https://play.google.com/apps/publish\" target=\"_blank\">Play Developer Console</a> by clicking your app -> Development Tools -> Services and APIs", true, "https://developer.android.com/training/in-app-billing/preparing-iab-app.html#AddToDevConsole", "https://lh3.googleusercontent.com/9WW-ovgK4v2va7xkI0Fu8ypaRGBCBboebwKMucGXvboZa1u9KIQkuCKPBzG80t6aIXo=w300");
        }
    }
    public function setIosMonthlySubscriptionCode(): void{
        if(is_object($this->getStoredMonetizationSettingsField('iosMonthlySubscriptionCode'))){
            $this->iosMonthlySubscriptionCode = $this->getStoredMonetizationSettings()->iosMonthlySubscriptionCode;
        }
        if(!$this->iosMonthlySubscriptionCode){
            $this->iosMonthlySubscriptionCode = new InputField("iOS Monthly Subscription Code", 'iosMonthlySubscriptionCode', $this->getClientId().'_monthly7', "string", "Create a monthly Auto-Renewable Subscription In-App Purchase at https://itunesconnect.apple.com and enter the code here", true, "https://itunesconnect.apple.com", "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR8IBe_Gu-i_gtUs0eaFwH1slGQoqgymL1MvUN9bcBz7rmA3zCExw");
        }
    }
    public function setIosYearlySubscriptionCode(): void{
        if(is_object($this->getStoredMonetizationSettingsField('iosYearlySubscriptionCode'))){
            $this->iosYearlySubscriptionCode = $this->getStoredMonetizationSettings()->iosYearlySubscriptionCode;
        }
        if(!$this->iosYearlySubscriptionCode){
            $this->iosYearlySubscriptionCode = new InputField("iOS Yearly Subscription Code", 'iosYearlySubscriptionCode', $this->getClientId().'_yearly7', "string", "Create a yearly Auto-Renewable Subscription In-App Purchase at https://itunesconnect.apple.com and enter the code here", true, "https://itunesconnect.apple.com", "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR8IBe_Gu-i_gtUs0eaFwH1slGQoqgymL1MvUN9bcBz7rmA3zCExw");
        }
    }
    /**
     * @return AppSettings|stdClass
     */
    public function getAppSettings(){
        return $this->appSettings;
    }
    /**
     * @return string|null
     */
    public function getClientId(): string{
        $appSettings = $this->getAppSettings();
        if(!$appSettings->clientId){
            le("No client id!");
        }
        return $appSettings->clientId;
    }
    /**
     * @return MonetizationSettings|null|stdClass
     */
    public function getStoredMonetizationSettings(){
        return $this->getAppSettings()->additionalSettings->monetizationSettings ?? null;
    }
    /**
     * @param $fieldName
     * @return mixed
     */
    public function getStoredMonetizationSettingsField(string $fieldName){
        $settings = $this->getStoredMonetizationSettings();
        $field = $settings->$fieldName ?? null;
		if(is_array($field) || is_object($field)){
			$field = InputField::instantiateIfNecessary($field);
		}
        return $field;
    }
    /**
     * @return bool
     */
    public function getHideBuyNowButtons(): bool{
        return $this->hideBuyNowButtons;
    }
    public function setHideBuyNowButtons(): void{
        if(is_object($this->getStoredMonetizationSettingsField('hideBuyNowButtons'))){
            $this->hideBuyNowButtons = $this->getStoredMonetizationSettings()->hideBuyNowButtons;
        }
        if(!$this->hideBuyNowButtons){
            // TODO: Remove after all clients are updated
            $value = (!is_object($this->getStoredMonetizationSettingsField('hideBuyNowButtons'))) ? $this->getStoredMonetizationSettingsField('hideBuyNowButtons') : false;
            $this->hideBuyNowButtons = new InputField("Hide Buy Now Buttons", 'hideBuyNowButtons', $value, "bool", "Would you like to hide the shopping cart buttons next to measurements and variables?", true, null, null);
        }
    }
}
