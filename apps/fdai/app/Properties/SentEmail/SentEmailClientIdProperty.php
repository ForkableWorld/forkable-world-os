<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\SentEmail;
use App\Models\SentEmail;
use App\Traits\PropertyTraits\SentEmailProperty;
use App\Properties\Base\BaseClientIdProperty;
class SentEmailClientIdProperty extends BaseClientIdProperty
{
    use SentEmailProperty;
    public $table = SentEmail::TABLE;
    public $parentClass = SentEmail::class;
}
