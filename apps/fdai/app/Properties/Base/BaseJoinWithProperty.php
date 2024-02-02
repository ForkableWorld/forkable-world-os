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
class BaseJoinWithProperty extends BaseProperty{
	use IsInt;
	public $dbInput = 'integer,false,true';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'The Variable this Variable should be joined with. If the variable is joined with some other variable then it is not shown to user in the list of variables';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::RAW_MEASUREMENTS_WITH_TAGS;
	public $htmlType = 'text';
	public $image = ImageUrls::RAW_MEASUREMENTS_WITH_TAGS;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'join_with';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'nullable|integer|min:1|max:2147483647';
	public $title = 'Join With';
	public $type = self::TYPE_INTEGER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|integer|min:1|max:2147483647';

}
