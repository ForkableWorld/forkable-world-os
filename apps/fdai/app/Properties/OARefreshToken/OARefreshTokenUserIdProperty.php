<?php
namespace App\Properties\OARefreshToken;
use App\Models\OARefreshToken;
use App\Traits\PropertyTraits\OARefreshTokenProperty;
use App\Properties\Base\BaseUserIdProperty;
class OARefreshTokenUserIdProperty extends BaseUserIdProperty
{
    use OARefreshTokenProperty;
    public $table = OARefreshToken::TABLE;
    public $parentClass = OARefreshToken::class;
}