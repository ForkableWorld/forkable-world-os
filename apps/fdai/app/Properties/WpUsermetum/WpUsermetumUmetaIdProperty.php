<?php
namespace App\Properties\WpUsermetum;
use App\Models\WpUsermetum;
use App\Traits\PropertyTraits\WpUsermetumProperty;
use App\Properties\Base\BaseUmetaIdProperty;
class WpUsermetumUmetaIdProperty extends BaseUmetaIdProperty
{
    use WpUsermetumProperty;
    public $table = WpUsermetum::TABLE;
    public $parentClass = WpUsermetum::class;
}