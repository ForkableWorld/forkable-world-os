<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Properties\User\UserProviderIdProperty;
use App\Properties\User\UserUserEmailProperty;
use App\Properties\User\UserUserLoginProperty;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\Types\QMStr;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use OpenApi\Generator;
class BaseDisplayNameProperty extends BaseNameProperty{
	use IsString;
    public const SYNONYMS = [
        'first_name',
        'displayName',
        'name',
        'nickname',
        'login'
    ];
	public $dbInput = 'string,250:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = 'Pretty display name';
	public $example = 'Zero User for WordPress';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::CARD;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::CREDENTIAL;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = true;
	public $maxLength = 250;
	public $name = self::NAME;
	public const NAME = 'display_name';
	public $canBeChangedToNull = true;
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:250';
	public $title = 'Display Name';
	public $type = PhpTypes::STRING;
	public $validations = 'nullable|max:250';
    /**
     * @param null $data
     * @return string
     */
    public static function getDefault($data = null): string{
        $name = BaseFirstNameProperty::getFirstAndLastNameFromArray($data, ' ');
        if (!empty($name)) {return $name;}
        $name = UserUserEmailProperty::pluck($data);
        if (!empty($name)) {return $name;}
		$login = UserUserLoginProperty::pluck($data);
        if(!$login){$login = UserProviderIdProperty::pluck($data);}
        if(!$login){le("No default value for display name");}
        return QMStr::titleCaseSlow($login);
    }
}
