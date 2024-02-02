<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseCardLastFourProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'string,4:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'card_last_four';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::CARD_LAST_FOUR;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::CARD_LAST_FOUR;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 4;
	public $name = self::NAME;
	public const NAME = 'card_last_four';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:4';
	public $title = 'Card Last Four';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|max:4';

}
