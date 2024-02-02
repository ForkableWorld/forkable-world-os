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
class BaseNctIdProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'string,4369:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'nct_id';
	public $example = 'NCT02826629';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::CLIENT_ID;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::CLIENT_ID;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 4369;
	public $name = self::NAME;
	public const NAME = 'nct_id';
	public $canBeChangedToNull = true;
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:4369';
	public $title = 'Nct';
	public $type = PhpTypes::STRING;
	public $validations = 'nullable|max:4369';

}
