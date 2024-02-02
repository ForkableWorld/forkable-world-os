<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsNumberOfRelated;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseNumberOfUsersProperty extends BaseProperty{
	use IsNumberOfRelated;
	public const NUMBER_OF_FAKE_USERS = 10;
	public $dbInput = 'integer,false';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Number of people who donated their data for this analysis.';
	public $example = 4;
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::OLD_USER;
	public $htmlType = 'text';
	public $image = ImageUrls::OLD_USER;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $canBeChangedToNull = false;
	public $isOrderable = true;
	public $isSearchable = false;
	public $maximum = 2147483647;
	public $minimum = 0;
	public $name = self::NAME;
	public const NAME = 'number_of_users';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'required|integer|min:0|max:2147483647';
	public $title = 'Users';
	public $type = self::TYPE_INTEGER;
	public $validations = 'required';
}
