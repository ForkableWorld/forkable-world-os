<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsInt;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use Doctrine\DBAL\Types\Types;
use OpenApi\Generator;
class BaseDurationProperty extends BaseProperty{
	use IsInt;
	public $dbInput = 'integer,false';
	public $dbType = Types::INTEGER;
	public $default = Generator::UNDEFINED;
	public $description = 'The number of seconds after the start time for which the measurement is still valid. ';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::CLOCK;
	public $htmlType = 'text';
	public $image = ImageUrls::CLOCK;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $maximum = 2147483647;
	public $minimum = 0;
	public $name = self::NAME;
	public const NAME = 'duration';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'nullable|integer|min:0|max:2147483647';
	public $title = 'Duration';
	public $type = self::TYPE_INTEGER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|integer|min:0|max:2147483647';
	public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
    }
}
