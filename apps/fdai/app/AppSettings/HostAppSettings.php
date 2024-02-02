<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings;
use App\Logging\QMLog;
use App\Models\Application;
use App\Properties\Base\BaseClientIdProperty;
use App\Storage\Memory;
use App\UI\InternalImageUrls;
class HostAppSettings extends AppSettings
{
	public static function getHostClientId(){
		return BaseClientIdProperty::getHostClientId();
	}
	/**
	 * @return AppSettings|null
	 */
    public static function instance(): ?AppSettings {
        return self::application()->getAppSettings();
    }
	/**
	 * @return Application
	 */
	public static function application(): Application {
		$hostClientId = BaseClientIdProperty::getHostClientId();
		$byClientId = Application::findByClientId($hostClientId);
		if(!$byClientId){
			QMLog::error("HostAppSettings::application() failed to find application by client id: $hostClientId. 
			Using default application: quantimodo.");
			return Application::quantimodo();
		}
		return $byClientId;
	}
	public static function iconUrl():string{
		if(self::isQM()){
			return InternalImageUrls::DEFAULT_ICON;
		}
		if(self::isCrowdsourcingCures()){
			return InternalImageUrls::ICONS_CROWDSOURCING_CURES_ICON_1024_1024_PADDING;
		}
		return self::application()->icon_url;
	}
	/**
	 * @return AppSettings|null
	 */
    public static function fromMemory(): ?AppSettings{
        return Memory::getHostAppSettings();
    }
	private static function clientId(): string{
		return BaseClientIdProperty::getHostClientId();
	}
	/**
	 * @return bool
	 */
	private static function isQM(): bool{
		return self::clientId() === BaseClientIdProperty::CLIENT_ID_QUANTIMODO;
	}
	/**
	 * @return bool
	 */
	private static function isCrowdsourcingCures(): bool{
		return self::clientId() === BaseClientIdProperty::CLIENT_ID_CROWDSOURCING_CURES;
	}
	public static function setClient(\App\Models\BaseModel|\App\Models\OAClient $client){
		Memory::setHostAppSettings($client->getAppSettings());
	}
}
