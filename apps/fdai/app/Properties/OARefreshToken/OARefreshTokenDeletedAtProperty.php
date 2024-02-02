<?php
namespace App\Properties\OARefreshToken;
use App\Models\OARefreshToken;
use App\Traits\PropertyTraits\OARefreshTokenProperty;
use App\Properties\Base\BaseDeletedAtProperty;
class OARefreshTokenDeletedAtProperty extends BaseDeletedAtProperty
{
    use OARefreshTokenProperty;
    public $table = OARefreshToken::TABLE;
    public $parentClass = OARefreshToken::class;
}