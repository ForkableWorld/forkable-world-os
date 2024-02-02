<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\VariableCategory;
use App\Traits\PropertyTraits\EnumProperty;
use App\Types\PhpTypes;
use App\UI\FontAwesome;
use App\UI\ImageUrls;

abstract class SometimesEnumProperty extends EnumProperty
{


    const SOMETIMES = 'SOMETIMES';
    const ALWAYS = 'ALWAYS';
    const NEVER = 'NEVER';
    public $canBeChangedToNull = false;
    public $default = self::SOMETIMES;
    public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
    public $image = ImageUrls::QUESTION_MARK;
    public $importance = false;
    public $isOrderable = false;
    public $isSearchable = true;
    public $showOnDetail = true;
    public $dbInput = 'string,25:nullable';
    public $dbType = PhpTypes::STRING;
    public $example = self::SOMETIMES;
    public $fieldType = PhpTypes::STRING;
    public $htmlInput = 'text';
    public $htmlType = 'text';
    public $inForm = true;
    public $inIndex = true;
    public $inView = true;
    public $isFillable = true;
    public $maxLength = 25;
    public $rules = 'nullable|max:25';
    public $type = self::TYPE_ENUM;
    public $validations = 'max:25';

    protected function isLowerCase(): bool
    {
        return false;
    }

    public function getEnumOptions(): array
    {
        return [self::SOMETIMES, self::ALWAYS, self::NEVER];
    }
	public static function populateIfEmpty(array $array): array {
		if (array_key_exists(static::NAME, $array) && empty($array[static::NAME])) {
			$array[static::NAME] = self::SOMETIMES;
		}
		return $array;
	}
}
