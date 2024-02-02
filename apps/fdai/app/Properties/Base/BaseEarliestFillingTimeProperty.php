<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\BaseModel;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\IsUnixtime;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseEarliestFillingTimeProperty extends BaseProperty{
    use IsUnixtime;
    public $isUnixTime = true;
	public $dbInput = 'integer,false';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Earliest date and time at which null values should be replaced with filler values, based on the earliest measurement from the data source or the variable.';
	public $example = 1572825600;
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::EARLIEST_REMINDER_TIME;
	public $htmlType = 'text';
	public $image = ImageUrls::EARLIEST_REMINDER_TIME;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $maximum = 2147483647;
	public $minimum = 946684801;
	public $name = self::NAME;
	public const NAME = 'earliest_filling_time';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'nullable|integer|min:946684801|max:2147483647';
	public $title = 'Earliest Filling Time';
	public $type = self::TYPE_INTEGER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|integer|min:946684801|max:2147483647';
    public function getLatestUnixTime(): int {
        $parent = $this->getParentModel();
        $latest_filling_time = $parent->latest_filling_time;
        return $latest_filling_time ?? BaseStartAtProperty::generateLatestUnixTime();
    }
    /**
     * @return UserVariable
     */
    public function getParentModel(): BaseModel {
        return parent::getParentModel();
    }
    public function showOnIndex(): bool{return false;}
    public function showOnDetail(): bool{return true;}
}
