<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\IsUnixtime;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use Doctrine\DBAL\Types\Types;
use OpenApi\Generator;
class BaseLatestFillingTimeProperty extends BaseProperty{
    use IsUnixtime;
    public $isUnixTime = true;
	public $dbInput = 'integer,false';
	public $dbType = Types::INTEGER;
	public $default = Generator::UNDEFINED;
	public $description = 'Latest filling time';
	public $example = 1576195200;
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::LATEST_REMINDER_TIME;
	public $htmlType = 'text';
	public $image = ImageUrls::LATEST_REMINDER_TIME;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $maximum = 2147483647;
	public $minimum = 946684801;
	public $name = self::NAME;
	public const NAME = 'latest_filling_time';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'nullable|integer|min:946684801|max:2147483647';
	public $title = 'Latest Filling Time';
	public $type = self::TYPE_INTEGER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|integer|min:946684801|max:2147483647';
	public function validate(): void {
        if(!$this->shouldValidate()){return;}
	    parent::validate();
        /** @var UserVariable $variable */
        $variable = $this->getParentModel();
	    $this->assertEarliestBeforeLatest($variable->earliest_filling_time, $this->getDBValue());
    }
    public function showOnIndex(): bool{return false;}
    public function showOnDetail(): bool{return true;}
}
