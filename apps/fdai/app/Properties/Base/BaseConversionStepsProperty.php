<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsArray;
use App\Storage\DB\QMDB;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseConversionStepsProperty extends BaseProperty{
	use IsArray;
	public $dbInput = 'text:nullable';
	public $dbType = QMDB::TYPE_TEXT;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'An array of mathematical operations, each containing a operation and value field to apply to the value in the current unit to convert it to the default unit for the unit category. ';
	public $example = [];
	public $fieldType = QMDB::TYPE_TEXT;
	public $fontAwesome = FontAwesome::UNIT_CONVERSION;
	public $htmlInput = 'textarea';
	public $htmlType = 'textarea';
	public $image = ImageUrls::UNIT_CONVERSION;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'conversion_steps';
	public $phpType = PhpTypes::ARRAY;
	public $title = 'Conversion Steps';
	public $type = PhpTypes::ARRAY;
    /** @noinspection PhpUnused */
    public function castDBValue(){
        $p = $this->getParentModel();
        $arr = $p->getAttribute($this->name);
        foreach($arr as $i => $item){
            ksort($item);
            $arr[$i] = $item;
        }
        $this->setRawAttribute(json_encode($arr));
    }
}
