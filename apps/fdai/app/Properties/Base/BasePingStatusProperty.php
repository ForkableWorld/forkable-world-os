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
class BasePingStatusProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'string,20:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'If the post allows <a href=\"http://codex.wordpress.org/Introduction_to_Blogging#Pingbacks\" target=\"_blank\">ping and trackbacks</a>.';
	public $example = 'closed';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::STATUS;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::ACTIVITIES_PING_PONG;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 20;
	public $name = self::NAME;
	public const NAME = 'ping_status';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:20';
	public $title = 'Ping Status';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|max:20';

}
