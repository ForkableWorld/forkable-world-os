<?php
namespace App\Properties\WpUsermetum;
use App\Models\WpUsermetum;
use App\Traits\PropertyTraits\WpUsermetumProperty;
use App\Properties\Base\BaseMetaKeyProperty;
class WpUsermetumMetaKeyProperty extends BaseMetaKeyProperty
{
    use WpUsermetumProperty;
    public $table = WpUsermetum::TABLE;
    public $parentClass = WpUsermetum::class;
}