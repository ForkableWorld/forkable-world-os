<?php
namespace App\Traits\PropertyTraits;
use App\Traits\HasModel\HasOARefreshToken;
use App\Models\OARefreshToken;
trait OARefreshTokenProperty
{
    use HasOARefreshToken;
    public function getOARefreshTokenId(): int{
        return $this->getParentModel()->getId();
    }
    public function getOARefreshToken(): OARefreshToken{
        return $this->getParentModel();
    }
}