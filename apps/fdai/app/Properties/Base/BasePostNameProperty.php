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
use App\Types\QMStr;
class BasePostNameProperty extends BaseNameProperty{
	use IsString;
    public const MAX_LENGTH = 200;
	public $dbInput = 'string,200:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'URL friendly slug of the post title.';
	public $example = 'i-am-a-post-name';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::POST;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::POST;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = true;
	public $maxLength = self::MAX_LENGTH;
	public $minLength = 3;
	public $name = self::NAME;
	public const NAME = 'post_name';
	public $phpType = PhpTypes::STRING;
	public $rules = 'required|string|max:200|min:3';
	public $title = 'Post Name';
	public $type = PhpTypes::STRING;
	public $validations = 'required|string|max:200|min:3';
    protected $shouldNotContain = [
        "https-",
        "-causes-",
        "examples-"
    ];
    protected $requiredStrings = [
        "-",
    ];
    public function getExample(): string{
        if($p = $this->parentModel){
            if($title = $p->post_title){
                $this->example = QMStr::slugify($title);
            }
        }
        return $this->example;
    }
}
