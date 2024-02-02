<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\ModelTraits;
use App\Buttons\RelationshipButtons\DeviceToken\DeviceTokenUserButton;
use App\Buttons\RelationshipButtons\RelationshipButton;
use App\Models\DeviceToken;
use App\Slim\Model\Notifications\QMDeviceToken;
trait DeviceTokenTrait {
	public function getDeviceToken(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[DeviceToken::FIELD_DEVICE_TOKEN] ?? null;
		} else{
			/** @var QMDeviceToken $this */
			return $this->deviceToken;
		}
	}
	public function setDeviceToken(string $deviceToken): void{
		$this->setAttribute(DeviceToken::FIELD_DEVICE_TOKEN, $deviceToken);
	}
	public function getDeletedAt(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[DeviceToken::FIELD_DELETED_AT] ?? null;
		} else{
			/** @var QMDeviceToken $this */
			return $this->deletedAt;
		}
	}
	public function setDeletedAt(string $deletedAt): void{
		$this->setAttribute(DeviceToken::FIELD_DELETED_AT, $deletedAt);
	}
	public function getLastCheckedAt(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[DeviceToken::FIELD_LAST_CHECKED_AT] ?? null;
		} else{
			/** @var QMDeviceToken $this */
			return $this->lastCheckedAt;
		}
	}
	public function setLastCheckedAt(string $lastCheckedAt): void{
		$this->setAttribute(DeviceToken::FIELD_LAST_CHECKED_AT, $lastCheckedAt);
	}
	public function setLastNotifiedAt(string $lastNotifiedAt): void{
		$this->setAttribute(DeviceToken::FIELD_LAST_NOTIFIED_AT, $lastNotifiedAt);
	}
	public function setPlatform(string $platform): void{
		$this->setAttribute(DeviceToken::FIELD_PLATFORM, $platform);
	}
	public function getReceivedAt(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[DeviceToken::FIELD_RECEIVED_AT] ?? null;
		} else{
			/** @var QMDeviceToken $this */
			return $this->receivedAt;
		}
	}
	public function setReceivedAt(string $receivedAt): void{
		$this->setAttribute(DeviceToken::FIELD_RECEIVED_AT, $receivedAt);
	}
	public function getServerHostname(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[DeviceToken::FIELD_SERVER_HOSTNAME] ?? null;
		} else{
			/** @var QMDeviceToken $this */
			return $this->serverHostname;
		}
	}
	public function setServerHostname(string $serverHostname): void{
		$this->setAttribute(DeviceToken::FIELD_SERVER_HOSTNAME, $serverHostname);
	}
	public function getServerIp(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[DeviceToken::FIELD_SERVER_IP] ?? null;
		} else{
			/** @var QMDeviceToken $this */
			return $this->serverIp;
		}
	}
	public function setServerIp(string $serverIp): void{
		$this->setAttribute(DeviceToken::FIELD_SERVER_IP, $serverIp);
	}
	/**
	 * @return RelationshipButton[]
	 */
	public function getInterestingRelationshipButtons(): array{
		return [
			new DeviceTokenUserButton($this),
		];
	}
}
