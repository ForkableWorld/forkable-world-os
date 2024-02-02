<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Exceptions\InvalidTimestampException;
use App\Exceptions\NoTimeZoneException;
use App\Models\BaseModel;
use App\Models\User;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\User\PublicUser;
use App\Slim\Model\User\QMUser;
use App\Types\TimeHelper;
use App\Utils\AppMode;
use Illuminate\Support\Carbon;
trait HasUser {
	abstract public function getUserId(): ?int;
	public function getUserButton(): QMButton{
		if(method_exists($this, 'relationLoaded') && $this->relationLoaded('user')){
			return $this->getUser()->getButton();
		}
		return User::generateDataLabShowButton($this->getUserId());
	}
	/**
	 * @return User|BaseModel
	 */
	public function getUser(): User{
		if($this instanceof User){
			return $this;
		}
		if(property_exists($this, 'parentModel') && $this->parentModel instanceof User){
			return $this->parentModel;
		}
		$u = $this->getUserFromMemory();
		if($u){
			return $u;
		}
		$id = $this->getUserId();
		return User::findInMemoryOrDB($id);
	}
	/**
	 * @return User|null
	 */
	public function getUserFromMemory(): ?User{
		$id = $this->getUserId();
        if(!$id){return null;}
		if($mem = User::findInMemory($id)){
			return $mem; // Prefer memory to relation because it's probably most up to date
		}
		if(method_exists($this, 'relationLoaded') && $this->relationLoaded('user')){
			return $this->user;
		}
		$v = QMUser::findInMemory($id);
		if($v){
			return $v->l();
		}
		return null;
	}
	public function getUserNameLink(): string{
		return $this->getUser()->getDataLabDisplayNameLink();
	}
	public function getUserName(): string{
		return $this->getUser()->getUserLogin();
	}
	public function getUserImageNameLink(): string{
		return $this->getUser()->getDataLabImageNameLink();
	}
	public function getUserIdLink(array $params = []): string{
		if($this instanceof User){
			return $this->getDataLabIdLink($params);
		}
		if(!$this->relationLoaded('user')){
			$userId = $this->getUserId();
			return User::generateDataLabShowLink($userId);
		}
		/** @var User $u */
		$u = $this->user;
		if(!$u){return 'No user for '.__FUNCTION__;}
		return $u->getDataLabIdLink($params);
	}
	public function getTruncatedUserLink(array $params = []): string{
		/** @var User $u */
		$u = $this->user;
		return $u->getTruncatedNameLink($params);
	}
	public function getUserLoginName(): string{
		return $this->getUser()->getUserLogin();
	}
	public function getQMUser(): QMUser{
		if($this instanceof QMUser){
			/** @noinspection PhpIncompatibleReturnTypeInspection */
			return $this;
		}
		return $this->getUser()->getQMUser();
	}
	/** @noinspection PhpUnused */
	public function getUserLink(array $params = []): string{
		return $this->getUser()->getNameLink($params);
	}
	public function hasUserId(): bool{
		return $this->getAttribute(self::FIELD_USER_ID) !== null;
	}
	/**
	 * @param string $accessType
	 * @param User|null $accessor
	 * @return bool
	 */
	public function patientGrantedAccess(string $accessType, User $accessor = null): bool{
		if(is_int($accessor)){
			$accessor = QMUser::find($accessor);
		}
		if(!$accessor){
			$accessor = QMAuth::getQMUser();
		}
		if(!$accessor && !AppMode::isApiRequest()){
			return false;
		}
		$ownerUserId = $this->getUserId();
		if(!$ownerUserId){
			le("No user id on " . $this);
		}
		if(!$accessor){
			return false;
		}
		$patients = $accessor->getPatients();
		foreach($patients as $patient){
			$scope = $patient->scope;
			if($patient->getId() === $ownerUserId && stripos($scope, $accessType) !== false){
				return true;
			}
		}
		return false;
	}
	/**
	 * @param int|string $utcUnixSecondsOrString
	 * @return string
	 */
	public function getHourAmPm($utcUnixSecondsOrString): string{
		return TimeHelper::getHourAmPm($utcUnixSecondsOrString, $this);
	}
	/**
	 * @param $utc
	 * @return \Carbon\Carbon
	 */
	public function convertToLocalTimezone($utc): \Carbon\Carbon{
		return $this->getUser()->convertToLocalTimezone($utc);
	}
	/**
	 * @param $datetime
	 * @return Carbon
	 * @throws NoTimeZoneException
	 */
	public function convertFromLocal($datetime): Carbon{ // https://github.com/jamesmills/laravel-timezone
		$str = db_date($datetime);
		return Carbon::parse($str, $this->getTimezone())->setTimezone('UTC');
	}
	/**
	 * @return string
	 * @throws \App\Exceptions\NoTimeZoneException
	 */
	public function getTimezone(): string{
		return $this->getUser()->getTimezone();
	}
	/**
	 * @param int|string $utcUnixSecondsOrString
	 * @return int
	 * @throws InvalidTimestampException
	 */
	public function convertUtcToEpochSecondsLocal($utcUnixSecondsOrString): ?int{
		$unixtimeUtc = TimeHelper::universalConversionToUnixTimestamp($utcUnixSecondsOrString);
		return $unixtimeUtc - $this->getTimeZoneOffsetInSeconds();
	}
	/**
	 * @param int|string $utcUnixSecondsOrString
	 * @return string
	 * @throws InvalidTimestampException
	 */
	public function convertUtcToLocalHourWeekdayAndDateString($utcUnixSecondsOrString): string{
		$localTimeStamp = $this->convertUtcToEpochSecondsLocal($utcUnixSecondsOrString);
		return TimeHelper::getHourWeekdayAndDateString($localTimeStamp);
	}
	/**
	 * @param int|string $utcUnixSecondsOrString
	 * @return string
	 * @throws InvalidTimestampException
	 */
	public function convertUtcWeekdayAndDateString($utcUnixSecondsOrString): string{
		$localTimeStamp = $this->convertUtcToEpochSecondsLocal($utcUnixSecondsOrString);
		return TimeHelper::getWeekdayAndDateString($localTimeStamp);
	}
	public function humanizeNotifyAt(string $utcStr): string{
		$this->convertToLocalTimezone(time());
		return $this->convertToLocalTimezone($utcStr)->calendar();
	}
	/**
	 * @return PublicUser
	 */
	public function getPublicUser(): PublicUser{
		return $this->getQMUser()->getPublicUser();
	}
}
