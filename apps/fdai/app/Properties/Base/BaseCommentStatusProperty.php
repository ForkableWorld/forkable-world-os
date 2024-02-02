<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\EnumProperty;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
class BaseCommentStatusProperty extends EnumProperty{

	public $dbInput = 'string,20:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'If comments are allowed.';
	public $example = 'closed';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::COMMENT_SOLID;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::COMMENT;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 20;
	public $name = self::NAME;
	public const NAME = 'comment_status';
	public $canBeChangedToNull = true;
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:20';
	public $title = 'Comment Status';
	public $type = PhpTypes::STRING;
	public $validations = 'nullable|max:20';
    const OPEN = 'open';
    const CLOSED = 'closed';
    public $enum = [self::OPEN,self::CLOSED,];
    protected function isLowerCase(): bool
    {
        return true;
    }
    public function getEnumOptions(): array
    {
        return $this->enum;
    }
}
