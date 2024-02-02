<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\WpPost;
use App\Models\WpPost;
use App\Traits\PropertyTraits\WpPostProperty;
use App\Properties\Base\BaseRecordSizeInKbProperty;
class WpPostRecordSizeInKbProperty extends BaseRecordSizeInKbProperty
{
    use WpPostProperty;
    public $table = WpPost::TABLE;
    public $parentClass = WpPost::class;
    public const LARGEST_FIELD = WpPost::FIELD_POST_CONTENT;
}
