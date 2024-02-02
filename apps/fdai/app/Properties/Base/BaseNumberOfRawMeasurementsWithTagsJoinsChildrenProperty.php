<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\Variable;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\IsInt;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use App\Variables\QMVariable;
use Doctrine\DBAL\Types\Types;
use OpenApi\Generator;
class BaseNumberOfRawMeasurementsWithTagsJoinsChildrenProperty extends BaseProperty{
	use IsInt;
	public $dbInput = 'integer,false,true';
	public $dbType = Types::INTEGER;
	public $default = Generator::UNDEFINED;
	public $description = 'number_of_raw_measurements_with_tags_joins_children';
	public $example = 48;
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::MEASUREMENTS;
	public $htmlType = 'text';
	public $image = ImageUrls::MEASUREMENTS;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $maximum = 2147483647;
	public $minimum = 0;
	public $name = self::NAME;
	public const NAME = 'number_of_raw_measurements_with_tags_joins_children';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'nullable|integer|min:0|max:2147483647';
	public $title = 'Raw Measurements With Tags Joins Children';
	public $type = self::TYPE_INTEGER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|integer|min:0|max:2147483647';
    use IsCalculated;
    /**
     * @param Variable|\App\Models\UserVariable $model
     * @return int
     * @noinspection PhpMissingReturnTypeInspection
     */
    public static function calculate($model){
        $measurements = $model->getMeasurementsWithTags();
        $val = count($measurements);
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
}
