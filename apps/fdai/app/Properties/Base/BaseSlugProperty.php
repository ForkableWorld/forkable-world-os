<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\Types\QMStr;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseSlugProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'string,200:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'slug';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::QUESTION_MARK;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 200;
	public $name = self::NAME;
	public const NAME = 'slug';
	public $canBeChangedToNull = true;
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:200';
	public $title = 'Slug';
	public $type = PhpTypes::STRING;
	public $validations = 'nullable|max:200';
	public static function populateIfEmpty(array $array): array {
		if (array_key_exists(static::NAME, $array) && empty($array[static::NAME])) {
			$name = BaseNameProperty::pluck($array);
			if($name){
				$array[static::NAME] = static::generate($name);
			}

		}
		return $array;
	}
	public static function generate(string $name): string{
		return QMStr::slugify($name);
	}
}
