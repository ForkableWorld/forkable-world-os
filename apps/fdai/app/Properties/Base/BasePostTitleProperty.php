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
class BasePostTitleProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'text,65535:nullable';
	public $dbType = 'text';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Title of the post.';
    public $example = "I am a big, black, and beautiful post title!";
	public $fieldType = 'text';
	protected $isPublic = true;
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
	public $minLength = 3;
	public $name = self::NAME;
	public const NAME = 'post_title';
	public $phpType = PhpTypes::STRING;
	public $rules = 'required|string|min:3';
	public $title = 'Post Title';
	public $type = self::TYPE_ENUM;
	public $validations = 'required|string|min:3';
    protected $shouldNotContain = [
        "Report Html",
        "Population for Population",
        "Systolic Top Number",
        "for System"
    ];
    protected $requiredStrings = [
        " "
    ];

}
