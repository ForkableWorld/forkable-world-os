<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Middleware\QMAuth;
use App\Storage\S3\S3Private;
use App\Traits\HasModel\HasUser;
use App\Utils\AppMode;
abstract class QMUserRelatedModel extends DBModel {
	use HasUser;
	public const FIELD_USER_ID = 'user_id';
	public const FIELD_CLIENT_ID = 'client_id';
	public $clientId;
	public $userId;
	private $logMetaDataString;
	/**
	 * Phrase constructor.
	 * @param int|null $userId
	 * @param string|null $clientId
	 */
	public function __construct(int $userId = null, string $clientId = null){
		if(!$this->clientId){
			$this->clientId = $clientId;
			if(!$this->clientId && AppMode::isApiRequest()){
				$this->clientId = BaseClientIdProperty::fromRequest(false);
			}
		}
		if(!$this->userId){
			$this->userId = $userId ?: QMAuth::getQMUser();
		}
	}
	public static function getS3Bucket(): string{ return S3Private::getBucketName(); }
	/**
	 * @return int
	 */
	public function getUserId(): ?int{
		/** @var int $userId */
		$userId = $this->userId;
		if(!$this->userId){
			$user = QMAuth::getQMUser();
			if($user){
				$userId = $user->id;
			}
		}
		return $this->userId = $userId;
	}
	/**
	 * @param int $userId
	 */
	public function setUserId(int $userId){
		$this->userId = $userId;
	}
	/**
	 * @return string
	 */
	public function getLogMetaDataString(): string{
		if($this->logMetaDataString){
			return $this->logMetaDataString;
		}
		return $this->logMetaDataString = $this->getQMUser()->getLogMetaDataString();
	}
	/**
	 * @return string
	 */
	public function getLogMetaDataSlug(): string{
		return $this->getSlugifiedClassName() . '-user-' . $this->getUserId();
	}
	/**
	 * @param string $name
	 * @param array $meta
	 * @param bool $obfuscate
	 * @param string|null $message
	 */
	public function logErrorOrInfoIfTesting(string $name, $meta = [], bool $obfuscate = true, string $message = null){
		if($this->getQMUser() && $this->getQMUser()->isTestUser()){
			$this->logInfo($message, $meta);
			return;
		}
		parent::logErrorOrInfoIfTesting($name, $meta, $obfuscate, $message);
	}
	/**
	 * @return string
	 */
	public function getFolderPath(): string{
		$path = '';
		if(AppMode::isTestingOrStaging()){
			$path .= "testing/";
		}
		$path .= 'users/';
		$path .= $this->getId();
		$path .= '/' . str_replace('user-', '', static::getPluralizedSlugifiedClassName()) . '/';
		$path .= $this->getId();
		return $path . '/';
	}
}
