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
class BasePostMimeTypeProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'string,100:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Only used for attachments, the MIME type of the uploaded file.';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::POST;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::POST;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $minLength = 3;
	public $name = self::NAME;
	public const NAME = 'post_mime_type';
	public $phpType = PhpTypes::STRING;
	public $rules = 'required|string|min:3';
	public $title = 'Post Mime Type';
	public $type = PhpTypes::STRING;
	public $validations = 'required|string|min:3';

}
