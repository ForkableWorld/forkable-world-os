<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseCommandProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsString;
	public $dbInput = 'string,191';
	public $dbType = 'string';
	public $default = 'undefined';
	public $description = 'command';
	public $example = 'sh vendor/bin/atoum';
	public $fieldType = 'string';
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::ESSENTIAL_COLLECTION_COMMAND;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'command';
	public $order = 99;
	public $phpType = 'string';
	public $showOnDetail = true;
	public $title = 'Command';
	public $type = 'string';

}
