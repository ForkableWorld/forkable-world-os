<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsHyperParameter;
use App\Traits\PropertyTraits\IsInt;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use App\Models\GlobalVariableRelationship;
use Doctrine\DBAL\Types\Types;
use OpenApi\Generator;
class BaseDurationOfActionProperty extends BaseProperty{
	use IsInt;
    use IsHyperParameter;
	public $dbInput = 'integer,false,true';
	public $dbType = Types::INTEGER;
	public $default = Generator::UNDEFINED;
	public $description = 'Estimated number of seconds following the onset delay in which a stimulus produces a perceivable effect.';
	public $example = 604800;
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::TIME_ZONE_OFFSET;
	public $htmlType = 'text';
	public $image = ImageUrls::JETBRAINS_ACTIONS_CHANGEVIEW_;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $canBeChangedToNull = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $maximum = 7776000;
	public $minimum = 600;
    public const NAME = GlobalVariableRelationship::FIELD_DURATION_OF_ACTION;
    public $name = self::NAME;
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'max:7776000|numeric|nullable';
	public $title = 'Duration of Action';
	public $type = self::TYPE_INTEGER;
	public $validations = 'max:7776000|numeric|nullable';
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
    }
}
