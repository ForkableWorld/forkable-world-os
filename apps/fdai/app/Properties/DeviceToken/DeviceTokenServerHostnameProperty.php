<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\DeviceToken;
use App\Models\DeviceToken;
use App\Traits\PropertyTraits\DeviceTokenProperty;
use App\Properties\Base\BaseServerHostnameProperty;
class DeviceTokenServerHostnameProperty extends BaseServerHostnameProperty
{
    use DeviceTokenProperty;
    public $table = DeviceToken::TABLE;
    public $parentClass = DeviceToken::class;
}
