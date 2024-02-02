<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsUnixtime;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use App\Types\TimeHelper;
class BaseStartTimeProperty extends BaseProperty{
    use IsUnixtime;
    public $isUnixTime = true;
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Start time for the measurement event in unixtime seconds since 1970';
	public $example = 1499256000;
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::ANALYSIS_STARTED;
	public $htmlType = 'text';
	public $image = ImageUrls::GETTING_STARTED;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $canBeChangedToNull = false;
	public $isOrderable = true;
	public $isSearchable = false;
    public $maximum = TimeHelper::YEAR_2030_UNIXTIME;
    public $minimum = 1;
	public $name = self::NAME;
	public const NAME = 'start_time';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'required|integer|min:1|max:'.TimeHelper::YEAR_2030_UNIXTIME;
	public $title = 'Start Time';
	public $type = self::TYPE_INTEGER;
	public $validations = 'required';
    public const SYNONYMS = [
        'start_time_epoch',
        'start_time',
        'tracking_reminder_notification_time_epoch',
        'timestamp',
    ];
    public function getLatestUnixTime(): int{
        return self::generateLatestUnixTime();
    }
    public static function generateLatestUnixTime(): int {
        return BaseStartAtProperty::generateLatestUnixTime();
    }
}
