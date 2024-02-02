<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsInt;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BasePostAuthorProperty extends BaseProperty{
	use IsInt;
	public $dbInput = 'bigInteger,false,true';
	public $dbType = 'bigint';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'The user ID who created it.';
	public $example = 1;
	public $fieldType = 'bigInteger';
	public $fontAwesome = FontAwesome::POST;
	public $htmlType = 'text';
	public $image = ImageUrls::POST;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $minimum = 1;
	public $name = self::NAME;
	public const NAME = 'post_author';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'required|numeric|min:1';
	public $title = 'Post Author';
	public $type = self::TYPE_INTEGER;
	public $validations = 'required|numeric|min:1';

}
