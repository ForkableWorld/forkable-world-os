<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\EnumProperty;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
class BaseScaleProperty extends EnumProperty {
	use IsString;
	public const INTERVAL = 'interval';
	public const NOMINAL  = 'nominal';
	public const ORDINAL  = 'ordinal';
	public const RATIO    = 'ratio';
	public $dbInput = PhpTypes::STRING;
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = '
Ordinal is used to simply depict the order of variables and not the difference between each of the variables. Ordinal scales are generally used to depict non-mathematical ideas such as frequency, satisfaction, happiness, a degree of pain etc.

Ratio Scale not only produces the order of variables but also makes the difference between variables known along with information on the value of true zero.

Interval scale contains all the properties of ordinal scale, in addition to which, it offers a calculation of the difference between variables. The main characteristic of this scale is the equidistant difference between objects. Interval has no pre-decided starting point or a true zero value.

Nominal, also called the categorical variable scale, is defined as a scale used for labeling variables into distinct classifications and doesn’t involve a quantitative value or order.
';
	public $example = self::NOMINAL;
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::BALANCE_SCALE_SOLID;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::BUSINESS_COLLECTION_JUSTICE_SCALE;
	public $isOrderable = false;
	public $enum = [
		self::INTERVAL,
		self::NOMINAL,
		self::ORDINAL,
		self::RATIO,
	];
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'scale';
	public $phpType = PhpTypes::STRING;
	public $title = 'Scale';
	public $type = PhpTypes::STRING;
	protected function isLowerCase(): bool{
		return true;
	}
	public function getEnumOptions(): array{
		return [
			self::NOMINAL,
			self::ORDINAL,
			self::INTERVAL,
			self::RATIO,
		];
	}
}
