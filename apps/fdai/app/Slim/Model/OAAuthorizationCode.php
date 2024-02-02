<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model;
class OAAuthorizationCode extends DBModel {
	public const TABLE = 'oa_authorization_codes';
	public static $PRIMARY_KEY = ['authorization_code' => 'authorization_code'];
	public const FIELD_authorization_code = 'authorization_code';
	public const FIELD_client_id = 'client_id';
	public const FIELD_created_at = 'created_at';
	public const FIELD_deleted_at = 'deleted_at';
	public const FIELD_EXPIRES = 'expires';
	public const FIELD_redirect_uri = 'redirect_uri';
	public const FIELD_SCOPE = 'scope';
	public const FIELD_updated_at = 'updated_at';
	public const FIELD_USER_ID = 'user_id';
	protected static $FOREIGN_KEYS = [];
	public $authorizationCode;
	public $clientId;
	public $createdAt;
	public $deletedAt;
	public $expires;
	public $redirectUri;
	public $scope;
	public $updatedAt;
	public $userId;
	/**
	 * @param string authorizationCode
	 * @return string
	 */
	public function setAuthorizationCode(string $authorizationCode){
		$originalValue = $this->authorizationCode;
		if($originalValue !== $authorizationCode){
			$this->modifiedFields['authorization_code'] = 1;
		}
		return $this->authorizationCode = $authorizationCode;
	}
	/**
	 * @param string clientId
	 * @return string
	 */
	public function setClientId(string $clientId){
		$originalValue = $this->clientId;
		if($originalValue !== $clientId){
			$this->modifiedFields['client_id'] = 1;
		}
		return $this->clientId = $clientId;
	}
	/**
	 * @param string deletedAt
	 * @return string
	 */
	public function setDeletedAt(string $deletedAt){
		$originalValue = $this->deletedAt;
		if($originalValue !== $deletedAt){
			$this->modifiedFields['deleted_at'] = 1;
		}
		return $this->deletedAt = $deletedAt;
	}
	/**
	 * @param string expires
	 * @return string
	 */
	public function setExpires(string $expires){
		$originalValue = $this->expires;
		if($originalValue !== $expires){
			$this->modifiedFields['expires'] = 1;
		}
		return $this->expires = $expires;
	}
	/**
	 * @param string redirectUri
	 * @return string
	 */
	public function setRedirectUri(string $redirectUri){
		$originalValue = $this->redirectUri;
		if($originalValue !== $redirectUri){
			$this->modifiedFields['redirect_uri'] = 1;
		}
		return $this->redirectUri = $redirectUri;
	}
	/**
	 * @param string scope
	 * @return string
	 */
	public function setScope(string $scope){
		$originalValue = $this->scope;
		if($originalValue !== $scope){
			$this->modifiedFields['scope'] = 1;
		}
		return $this->scope = $scope;
	}
	/**
	 * @param string userId
	 * @return string
	 */
	public function setUserId(string $userId){
		$originalValue = $this->userId;
		if($originalValue !== $userId){
			$this->modifiedFields['user_id'] = 1;
		}
		return $this->userId = $userId;
	}
	/**
	 * @return string
	 */
	public function getAuthorizationCode(): string{
		$authorizationCode = $this->authorizationCode;
		return $authorizationCode;
	}
	/**
	 * @return string
	 */
	public function getClientId(): string{
		$clientId = $this->clientId;
		return $clientId;
	}
	/**
	 * @return string
	 */
	public function getCreatedAt(): string{
		$createdAt = $this->createdAt;
		return $createdAt;
	}
	/**
	 * @return string
	 */
	public function getDeletedAt(): string{
		$deletedAt = $this->deletedAt;
		return $deletedAt;
	}
	/**
	 * @return string
	 */
	public function getExpires(): string{
		$expires = $this->expires;
		return $expires;
	}
	/**
	 * @return string
	 */
	public function getRedirectUri(): string{
		$redirectUri = $this->redirectUri;
		return $redirectUri;
	}
	/**
	 * @return string
	 */
	public function getScope(): string{
		$scope = $this->scope;
		return $scope;
	}
	/**
	 * @return string
	 */
	public function getUpdatedAt(): string{
		$updatedAt = $this->updatedAt;
		return $updatedAt;
	}
	/**
	 * @return string
	 */
	public function getUserId(): string{
		$userId = $this->userId;
		return $userId;
	}
}
