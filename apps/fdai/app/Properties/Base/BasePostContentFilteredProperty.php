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
class BasePostContentFilteredProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'text:nullable';
	public $dbType = 'text';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Used by plugins to cache a version of post_content typically passed through the ‘the_content’ filter. Not used by WordPress core itself.';
	public $fieldType = 'text';
	public $fontAwesome = FontAwesome::POST;
	public $htmlInput = 'textarea';
	public $htmlType = 'textarea';
	public $image = ImageUrls::POST;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'post_content_filtered';
	public $phpType = PhpTypes::STRING;
	public $rules = 'string|nullable';
	public $title = 'Post Content Filtered';
	public $type = PhpTypes::STRING;
	public $validations = 'string|nullable';
    public bool $deprecated = true;

}
