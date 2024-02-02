<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\OAClient;
use App\Models\OAClient;
use App\Traits\PropertyTraits\OAClientProperty;
use App\Properties\Base\BaseNumberOfVariablesProperty;
class OAClientNumberOfVariablesProperty extends BaseNumberOfVariablesProperty
{
    use OAClientProperty;
    public $table = OAClient::TABLE;
    public $parentClass = OAClient::class;
}
