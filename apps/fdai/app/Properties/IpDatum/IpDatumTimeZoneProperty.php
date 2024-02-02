<?php
namespace App\Properties\IpDatum;
use App\Models\IpDatum;
use App\Properties\BaseProperty;
use App\Traits\PropertyTraits\IpDatumProperty;
use App\UI\FontAwesome;
use App\UI\ImageUrls;

class IpDatumTimeZoneProperty extends BaseProperty
{
    use IpDatumProperty;
    public $table = IpDatum::TABLE;
    public $parentClass = IpDatum::class;
    use \App\Traits\PropertyTraits\IsString;
    public $canBeChangedToNull = true;
    public $dbInput = 'text';
    public $dbType = 'text';
    public $default = 'undefined';
    public $description = 'Example: {id:America/Los_Angeles,current_time:2018-03-29T07:35:08-07:00,gmt_offset:-25200,code:PDT,is_daylight_saving:true}';
    public $fieldType = 'text';
    public $fontAwesome = FontAwesome::EARLIEST_REMINDER_TIME;
    public $htmlInput = 'textarea';
    public $htmlType = 'textarea';
    public $image = ImageUrls::TIME;
    public $importance = false;
    public $inForm = true;
    public $inIndex = true;
    public $inView = true;
    public $isFillable = true;
    public $isOrderable = false;
    public $isSearchable = true;
    public $name = self::NAME;
    public const NAME = 'time_zone';
    public $order = 99;
    public $phpType = 'string';
    public $showOnDetail = true;
    public $title = 'Time Zone';
    public $type = 'string';
    public $validations = 'nullable|string|nullable|string|nullable|string';
}
