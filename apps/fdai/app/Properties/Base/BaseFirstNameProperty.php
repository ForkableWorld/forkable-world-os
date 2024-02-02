<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
class BaseFirstNameProperty extends BaseNameProperty{
	use IsString;
    public const SYNONYMS = [
        'first_name',
        'given_name'
    ];
	public $dbInput = 'string,255:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'first_name';
	public $example = 'Demo';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::FIRST_NAME;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::FIRST_NAME;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = true;
	public $maxLength = 255;
	public $name = self::NAME;
	public const NAME = 'first_name';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:255';
	public $title = 'First Name';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|max:255';
    /**
     * @param $profile
     * @param string $delimiter
     * @return string
     */
    public static function getFirstAndLastNameFromArray($profile, string $delimiter){
        $firstName = self::pluck($profile);
        $lastName = BaseLastNameProperty::pluck($profile);
        if (!empty($firstName) && !empty($lastName)) {
            return $firstName . $delimiter . $lastName;
        }
        return null;
    }
}
