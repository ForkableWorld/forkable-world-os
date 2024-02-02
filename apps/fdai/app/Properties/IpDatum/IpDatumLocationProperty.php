<?php
namespace App\Properties\IpDatum;
use App\Models\IpDatum;
use App\Traits\PropertyTraits\IpDatumProperty;
use App\Properties\Base\BaseLocationProperty;
use App\Traits\PropertyTraits\IsArray;

class IpDatumLocationProperty extends BaseLocationProperty
{
    use IpDatumProperty, IsArray;
    public $table = IpDatum::TABLE;
    public $parentClass = IpDatum::class;
}
