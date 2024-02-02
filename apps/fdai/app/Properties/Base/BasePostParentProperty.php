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
class BasePostParentProperty extends BaseProperty{
	use IsInt;
	public $dbInput = 'bigInteger,false,true';
	public $dbType = 'bigint';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Used to create a relationship between this post and another when this post is a revision, attachment or another type.';
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
	public $minimum = 0;
	public $name = self::NAME;
	public const NAME = 'post_parent';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'nullable|numeric|min:0';
	public $title = 'Post Parent';
	public $type = self::TYPE_INTEGER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|numeric|min:0';

}
