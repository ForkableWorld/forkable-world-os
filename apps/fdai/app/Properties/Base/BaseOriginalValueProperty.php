<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsFloat;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Types\QMStr;
use App\Slim\Model\Measurement\QMMeasurement;
class BaseOriginalValueProperty extends BaseValueProperty{
	use IsFloat;
	public $dbInput = 'float,10,0';
	public $dbType = 'float';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Value of measurement as originally posted (before conversion to default unit)';
	public $example = 3;
	public $fieldType = 'float';
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $htmlType = 'text';
	public $image = ImageUrls::QUESTION_MARK;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $canBeChangedToNull = false;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'original_value';
	public $phpType = 'float';
	public $rules = 'required|numeric';
	public $title = 'Original Value';
	public $type = 'number';
	public $validations = 'required';
    /**
     * @param $originalValue
     * @return float
     */
    public static function toFloat($originalValue): float{
        lei ($originalValue === null,"Value should not be null!");
        if (is_string($originalValue)) {
            if (strtolower($originalValue) === QMMeasurement::STRING_NO) {
                return (float)0;
            }
            if (strtolower($originalValue) === QMMeasurement::STRING_YES) {
                return (float)1;
            }
            if (!is_numeric($originalValue)) {
                return QMStr::wordsToNumber($originalValue);
            }
        }
		if(!is_numeric($originalValue)){le( "modifiedValue must be numeric but is $originalValue!");}
        return (float)$originalValue;
    }
}
