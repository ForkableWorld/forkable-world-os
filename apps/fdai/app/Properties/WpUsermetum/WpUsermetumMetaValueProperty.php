<?php
namespace App\Properties\WpUsermetum;
use App\Models\WpUsermetum;
use App\Traits\PropertyTraits\WpUsermetumProperty;
use App\Properties\Base\BaseMetaValueProperty;
class WpUsermetumMetaValueProperty extends BaseMetaValueProperty
{
    use WpUsermetumProperty;
    public $table = WpUsermetum::TABLE;
    public $parentClass = WpUsermetum::class;
}