<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsUrl;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseWikipediaUrlProperty extends BaseProperty{
	use IsUrl;
	public $dbInput = 'string,2083:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'wikipedia_url';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::WIKIPEDIA_W;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::USER_URL;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 2083;
	public $name = self::NAME;
	public const NAME = 'wikipedia_url';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:2083|url';
	public $title = 'Wikipedia Url';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|max:2083|url';

}
