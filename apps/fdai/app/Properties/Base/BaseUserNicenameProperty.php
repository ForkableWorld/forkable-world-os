<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Properties\User\UserUserLoginProperty;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseUserNicenameProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'string,50:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Display name for the user.';
	public $example = 'zero';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::USER_NICENAME;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::COLLABORATOR;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = true;
	public $maxLength = 50;
	public $name = self::NAME;
	public const NAME = 'user_nicename';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:50';
	public $title = 'User Nicename';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|max:50';

    public static function getDefault($data = null)
    {
        return UserUserLoginProperty::pluck($data);
    }

}
