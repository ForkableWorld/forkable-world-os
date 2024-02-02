<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsFloat;
use App\Models\UserVariable;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use OpenApi\Generator;
class BaseThirdToLastValueProperty extends BaseValueProperty{
	use IsFloat;
	public $dbInput = 'float,10,0';
	public $dbType = 'float';
	public $default = Generator::UNDEFINED;
	public $description = 'third_to_last_value';
	public $example = 100;
	public $fieldType = 'float';
	public $fontAwesome = FontAwesome::THIRD_PARTY_CORRELATION;
	public $htmlType = 'text';
	public $image = ImageUrls::CARD_LAST_FOUR;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'third_to_last_value';
	public $phpType = 'float';
	public $rules = 'nullable|numeric';
	public $title = 'Third to Last Value';
	public $type = self::TYPE_NUMBER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|numeric';
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
        $this->assertNotEqualsAnotherAttributeUnlessNull(UserVariable::FIELD_LAST_VALUE);
        $this->assertNotEqualsAnotherAttributeUnlessNull(UserVariable::FIELD_SECOND_TO_LAST_VALUE);
    }
    public function getExample(): ?float {return null;}
}
