<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\EnumProperty;
use App\Types\PhpTypes;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use OpenApi\Generator;
class BasePostTypeProperty extends EnumProperty{
    public const TYPE_POST = 'post';
    public const TYPE_ATTACHMENT = 'attachment';
    public $dbInput = 'string,20:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = 'The content type identifier.';
	public $example = 'page';
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
	public const NAME = 'post_type';
	public $phpType = PhpTypes::STRING;
	public $rules = 'required|string|min:3';
	public $title = 'Post Type';
	public $type = self::TYPE_ENUM;
	public $validations = 'required|string|min:3';
	public $enum = [
	  BasePostTypeProperty::TYPE_POST,
      BasePostTypeProperty::TYPE_ATTACHMENT,
    ];
    protected function isLowerCase():bool{return true;}
	public function getEnumOptions(): array{return $this->enum;}
}
