<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BasePermissionsProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsString;
	public $dbInput = 'text';
	public $dbType = 'text';
	public $default = 'undefined';
	public $description = 'Example: {admin:true,maintain:true,push:true,triage:true,pull:true}';
	public $example = 'Array';
	public $fieldType = 'text';
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $htmlInput = 'textarea';
	public $htmlType = 'textarea';
	public $image = ImageUrls::QUESTION_MARK;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'permissions';
	public $order = 99;
	public $phpType = \App\Types\PhpTypes::ARRAY;
	public $rules = 'required';
	public $showOnDetail = true;
	public $title = 'Permissions';
	public $type = \App\Types\PhpTypes::ARRAY;
	public $validations = 'required';

}
