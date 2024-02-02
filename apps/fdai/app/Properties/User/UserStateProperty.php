<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\User;
use App\Models\User;
use App\Properties\BaseProperty;
use App\Traits\PropertyTraits\IsString;
use App\Traits\PropertyTraits\UserProperty;
use App\Types\PhpTypes;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use OpenApi\Generator;
class UserStateProperty extends BaseProperty
{
    use UserProperty;
	use IsString;
	public $dbInput = 'string,255:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = 'state';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::STATE;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::STATE;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 255;
	public $name = self::NAME;
	public const NAME = 'state';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:255';
	public $title = 'State';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|max:255';
    public $table = User::TABLE;
    public $parentClass = User::class;
}
