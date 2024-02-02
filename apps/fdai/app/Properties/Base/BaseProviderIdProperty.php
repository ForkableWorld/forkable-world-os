<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\User;
use App\Properties\BaseProperty;
use App\Properties\User\UserIdProperty;
use App\Traits\PropertyTraits\AdminProperty;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use OpenApi\Generator;
class BaseProviderIdProperty extends BaseProperty{
	use IsString, AdminProperty;
    public const SYNONYMS = [
        User::FIELD_PROVIDER_ID,
        //'user_id',
        'uuid',
        'your_user_id',
	    'client_user_id',
    ];
	public $dbInput = 'string,255:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = 'Unique id from provider';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::CARD;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::PROVIDER;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 255;
	public $name = self::NAME;
	public const NAME = 'provider_id';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:255';
	public $title = 'Provider';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|max:255';
	/**
     * @param $data
     * @param string|null $clientUserId
     * @return string|int|null
     */
    public static function getProviderIdFromArray($data, ?string $clientUserId = null){
        $id = parent::pluck($data);
        if($id){
            return $id;
        }
        return $clientUserId ?: UserIdProperty::pluck($data);
    }
}
