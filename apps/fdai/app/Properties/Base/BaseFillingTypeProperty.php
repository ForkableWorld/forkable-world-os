<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\EnumProperty;
use App\Traits\PropertyTraits\IsHyperParameter;
use App\Types\PhpTypes;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use OpenApi\Generator;
class BaseFillingTypeProperty extends EnumProperty{
    use IsHyperParameter;
    public const FILLING_TYPE_ZERO = 'zero';
    public const FILLING_TYPE_UNDEFINED = Generator::UNDEFINED;
    public const FILLING_TYPE_NONE = 'none';
    public const FILLING_TYPE_VALUE = 'value';
    public const FILLING_TYPE_INTERPOLATION = 'interpolation';
    public $dbInput = 'string:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = 'How gaps without any measurements should be treated. Options are none or zero.';
	public $example = 'none';
	public $enum = [
	    self::FILLING_TYPE_VALUE,
        self::FILLING_TYPE_ZERO,
        self::FILLING_TYPE_NONE,
        self::FILLING_TYPE_UNDEFINED,
        self::FILLING_TYPE_INTERPOLATION,
    ];
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::QUESTION_MARK;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'filling_type';
	public $canBeChangedToNull = true;
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|in:none,zero';
	public $title = 'Filling Type';
	public $type = self::TYPE_ENUM;
	public $validations = 'nullable|in:none,zero';
	/**
	 * @param $val
	 * @return string
	 */
	public static function valueToType($val): string{
        return BaseFillingValueProperty::fillingValueToType($val);
    }
	/**
	 * @param $val
	 * @return string
	 */
	public static function fromValue($val): string{
        return self::valueToType($val);
    }
    public static function toValue(string $type, float $value = null): ?float {
        return BaseFillingValueProperty::fromType($type, $value);
    }
    public static function hasFillingValue(string $type): bool{
        return $type === BaseFillingTypeProperty::FILLING_TYPE_VALUE ||
            $type === BaseFillingTypeProperty::FILLING_TYPE_ZERO;
    }
    public function shouldShowFilter(): bool{return false;}
    protected function isLowerCase():bool{return true;}
	public function getEnumOptions(): array{return $this->enum;}
}
