<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Logging\QMLog;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use stdClass;
class BaseUpcOneFourProperty extends BaseProperty {
	use IsString;
	public $dbInput = 'string,255:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'upc_14';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::BUSINESS_COLLECTION_CART_14;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 255;
	public $name = self::NAME;
	public const NAME = 'upc_14';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:255';
	public $title = 'Upc 14';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|max:255';
	public function authorizeUpdate(): void{ $this->authorizeIfLoggedIn(); }
}
