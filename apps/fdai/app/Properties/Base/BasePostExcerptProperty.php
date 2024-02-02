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
class BasePostExcerptProperty extends BaseProperty{
	use IsString;
    protected $isPublic = true;
	public $dbInput = 'text,65535:nullable';
	public $dbType = 'text';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Custom intro or short version of the content.';
	public $example = "I am a big, black, and beautiful post excerpt!";
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
	public $maxLength = 65535;
	public $minLength = 10;
	public $name = self::NAME;
	public const NAME = 'post_excerpt';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|min:10|max:65535';
	public $title = 'Post Excerpt';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|min:10|max:65535';
    protected $shouldNotContain = [
        "\n",
    ];
    protected $requiredStrings = [
        " ",
    ];
}
