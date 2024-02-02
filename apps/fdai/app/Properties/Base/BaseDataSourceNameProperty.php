<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipDataSourceNameProperty;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\VariableRelationships\QMVariableRelationship;
use App\Slim\Model\StaticModel;
class BaseDataSourceNameProperty extends BaseNameProperty {
	use IsString;
	public $dbInput = 'string,255:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'data_source_name';
	public $example = 'user';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::ACTIVITY;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::CONNECTOR_DEVICE;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = true;
	public $maxLength = 255;
	public $name = self::NAME;
	public const NAME = 'data_source_name';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:255';
	public $title = 'Data Source Name';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|max:255';

    /**
     * @return array
     */
    public static function get3rdPartyDataSourceNames(): array
    {
        $names = self::getDataSourceNames();
        $thirdPartyNames = [];
        foreach ($names as $key => $value) {
            if ($value !== GlobalVariableRelationshipDataSourceNameProperty::DATA_SOURCE_NAME_USER) {
                $thirdPartyNames[] = $value;
            }
        }
        return $thirdPartyNames;
    }
    /**
     * @return array
     */
    public static function getDataSourceNames(): array{
        $names = StaticModel::getConstantValuesWithNameLike('DATA_SOURCE_NAME', 'FIELD');
        return array_values($names);
    }
}
