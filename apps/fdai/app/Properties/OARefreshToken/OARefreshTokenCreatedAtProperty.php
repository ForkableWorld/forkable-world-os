<?php
namespace App\Properties\OARefreshToken;
use App\Models\OARefreshToken;
use App\Traits\PropertyTraits\OARefreshTokenProperty;
use App\Properties\Base\BaseCreatedAtProperty;
class OARefreshTokenCreatedAtProperty extends BaseCreatedAtProperty
{
    use OARefreshTokenProperty;
    public $table = OARefreshToken::TABLE;
    public $parentClass = OARefreshToken::class;
}