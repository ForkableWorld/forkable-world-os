<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\OAAccessToken;
use App\Models\OAAccessToken;
use App\Traits\PropertyTraits\OAAccessTokenProperty;
use App\Properties\Base\BaseUpdatedAtProperty;
class OAAccessTokenUpdatedAtProperty extends BaseUpdatedAtProperty
{
    use OAAccessTokenProperty;
    public $table = OAAccessToken::TABLE;
    public $parentClass = OAAccessToken::class;
}
