<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\ModelTraits;
use App\Buttons\RelationshipButtons\OAAccessToken\OAAccessTokenUserButton;
use App\Buttons\RelationshipButtons\RelationshipButton;
use App\Models\OAAccessToken;
use App\Slim\Model\Auth\QMAccessToken;
trait OAAccessTokenTrait {
	public function getAccessToken(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAAccessToken::FIELD_ACCESS_TOKEN] ?? null;
		} else{
			/** @var QMAccessToken $this */
			return $this->accessToken;
		}
	}
	public function setAccessToken(string $accessToken): void{
		$this->setAttribute(OAAccessToken::FIELD_ACCESS_TOKEN, $accessToken);
	}
	public function getDeletedAt(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAAccessToken::FIELD_DELETED_AT] ?? null;
		} else{
			/** @var QMAccessToken $this */
			return $this->deletedAt;
		}
	}
	public function setDeletedAt(string $deletedAt): void{
		$this->setAttribute(OAAccessToken::FIELD_DELETED_AT, $deletedAt);
	}
	public function getExpires(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAAccessToken::FIELD_EXPIRES] ?? null;
		} else{
			/** @var QMAccessToken $this */
			return $this->expires;
		}
	}
	public function setExpires(string $expires): void{
		$this->setAttribute(OAAccessToken::FIELD_EXPIRES, $expires);
	}
	public function getScope(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAAccessToken::FIELD_SCOPE] ?? null;
		} else{
			/** @var QMAccessToken $this */
			return $this->scope;
		}
	}
	public function setScope(string $scope): void{
		$this->setAttribute(OAAccessToken::FIELD_SCOPE, $scope);
	}
	public function setUserId(int $userId): void{
		$this->setAttribute(OAAccessToken::FIELD_USER_ID, $userId);
	}
	/**
	 * @return RelationshipButton[]
	 */
	public function getInterestingRelationshipButtons(): array{
		return [
			new OAAccessTokenUserButton($this),
		];
	}
}
