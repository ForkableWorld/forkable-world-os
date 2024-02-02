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
class BaseCoverPhotoProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'string,2083:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'cover_photo';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::COVER_PHOTO;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::COVER_PHOTO;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 2083;
	public $name = self::NAME;
	public const NAME = 'cover_photo';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:2083';
	public $title = 'Cover Photo';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|max:2083';

}
