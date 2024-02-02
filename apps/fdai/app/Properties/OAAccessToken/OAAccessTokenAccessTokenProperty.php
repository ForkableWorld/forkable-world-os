<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\OAAccessToken;
use App\Models\OAAccessToken;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Traits\PropertyTraits\IsPrimaryKey;
use App\Traits\PropertyTraits\OAAccessTokenProperty;
class OAAccessTokenAccessTokenProperty extends BaseAccessTokenProperty{
    use OAAccessTokenProperty;
    use IsPrimaryKey;
    public $isPrimary = true;
    public $table = OAAccessToken::TABLE;
    public $parentClass = OAAccessToken::class;
}
