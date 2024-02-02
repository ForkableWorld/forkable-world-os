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
class BaseStudyStatusProperty extends EnumProperty {
	use IsString;
	public $dbInput = 'string,20';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'study_status';
	public $example = 'publish';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::CREATE_STUDY;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::STUDY;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'study_status';
	public $phpType = PhpTypes::STRING;
	public $rules = 'in:draft,publish,private';
	public $title = 'Study Status';
	public $type = PhpTypes::STRING;
	public $validations = 'required';
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISH = 'publish';
    const STATUS_PRIVATE = 'private';
    public $enum = [self::STATUS_DRAFT,self::STATUS_PUBLISH,self::STATUS_PRIVATE,];

    protected function isLowerCase(): bool
    {
        return true;
    }

    public function getEnumOptions(): array
    {
        return $this->enum;
    }
}
