<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\SentEmail;
use App\Models\SentEmail;
use App\Traits\PropertyTraits\SentEmailProperty;
use App\Properties\Base\BaseUserIdProperty;
use App\Traits\HasUserFilter;
class SentEmailUserIdProperty extends BaseUserIdProperty
{
    use SentEmailProperty;
    use HasUserFilter;
    public $table = SentEmail::TABLE;
    public $parentClass = SentEmail::class;
}
