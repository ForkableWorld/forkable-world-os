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
class BaseNameSingularProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'string,255';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'The singular version of the name.';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::DISPLAY_NAME;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::DISPLAY_NAME;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'name_singular';
    public $phpType = PhpTypes::STRING;
	public $showOnDetail = true;
	public $title = 'Name Singular';
	public $type = PhpTypes::STRING;

}
