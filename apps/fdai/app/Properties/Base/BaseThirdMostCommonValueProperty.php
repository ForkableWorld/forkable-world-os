<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\Variable;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use OpenApi\Generator;
class BaseThirdMostCommonValueProperty extends BaseValueProperty{
	public $dbInput = 'float,10,0';
	public $dbType = 'float';
	public $default = Generator::UNDEFINED;
	public $description = 'third_most_common_value';
	public $fieldType = 'float';
	public $fontAwesome = FontAwesome::COMMON_TAG;
	public $htmlType = 'text';
	public $image = ImageUrls::COMMON_TAG;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'third_most_common_value';
	public $phpType = 'float';
	public $rules = 'nullable|numeric';
	public $title = 'Third Most Common Value';
	public $type = self::TYPE_NUMBER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|numeric';
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
        $this->assertNotEqualsAnotherAttributeUnlessNull(Variable::FIELD_MOST_COMMON_VALUE);
        $this->assertNotEqualsAnotherAttributeUnlessNull(Variable::FIELD_SECOND_MOST_COMMON_VALUE);
    }
    public function getExample(): ?float {return null;}
}
