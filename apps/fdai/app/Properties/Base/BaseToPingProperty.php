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
class BaseToPingProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'text,65535:nullable';
	public $dbType = 'text';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'A list of URLs WordPress should send pingbacks to when updated.';
	public $fieldType = 'text';
	public $fontAwesome = FontAwesome::HANDS_HELPING_SOLID;
	public $htmlInput = 'textarea';
	public $htmlType = 'textarea';
	public $image = ImageUrls::ACTIVITIES_PING_PONG;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 65535;
	public $name = self::NAME;
	public const NAME = 'to_ping';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:65535';
	public $title = 'To Ping';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|max:65535';

}
