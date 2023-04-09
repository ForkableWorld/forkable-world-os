<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\WpPost;
use App\Models\WpPost;
use App\Traits\PropertyTraits\WpPostProperty;
use App\Properties\Base\BaseCreatedAtProperty;
class WpPostCreatedAtProperty extends BaseCreatedAtProperty
{
    use WpPostProperty;
    public $table = WpPost::TABLE;
    public $parentClass = WpPost::class;
}