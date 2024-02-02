<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserClient;
use App\Models\UserClient;
use App\Properties\Base\BaseProviderIdProperty;
use App\Traits\PropertyTraits\UserClientProperty;
use App\Properties\Base\BaseUserIdProperty;
use App\Traits\HasUserFilter;
class UserClientUserIdProperty extends BaseUserIdProperty
{
    use UserClientProperty;
    use HasUserFilter;
    public $table = UserClient::TABLE;
    public $parentClass = UserClient::class;
	public const SYNONYMS = BaseProviderIdProperty::SYNONYMS;
}
