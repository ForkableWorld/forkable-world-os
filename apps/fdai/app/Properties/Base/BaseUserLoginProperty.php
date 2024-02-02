<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Exceptions\InvalidUsernameException;
use App\Logging\QMLog;
use App\Models\User;
use App\Properties\BaseProperty;
use App\Properties\User\UserProviderIdProperty;
use App\Properties\User\UserUserEmailProperty;
use App\Properties\User\UserUserLoginProperty;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\Types\QMStr;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use OpenApi\Generator;
use TheIconic\Tracking\GoogleAnalytics\Parameters\User\ClientId;

class BaseUserLoginProperty extends BaseProperty{
	use IsString;
    public const SYNONYMS = [
        User::FIELD_USER_LOGIN,
        'log',
        'username',
        'login',
        'nickname',
	    'screen_name'
    ];
	public const TEST_USERNAME_18535 = 'testuser';
	public const USER_LOGIN_ECONOMIC_DATA = "economic-data";
    public const TEST_USERNAME_QUANTIMODO = 'quantimodo';
    public $dbInput = 'string,60:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = 'Unique username for the user.';
	public $example = 'zero';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::LOGIN;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::LOGIN;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 60;
	public $minLength = self::MIN_LENGTH;
	public const MIN_LENGTH = 2;
	public $name = self::NAME;
	public const NAME = 'user_login';
	public $phpType = PhpTypes::STRING;
	public $rules = 'required|min:2|max:60';
	public $title = 'User Login';
	public $type = PhpTypes::STRING;
	public $validations = 'required|min:2|max:60';
    /**
     * @param null $data
     * @return string
     */
    public static function getDefault($data = null): string{
        $name = BaseFirstNameProperty::getFirstAndLastNameFromArray($data, '-');
        if(empty($name)){
            $email = UserUserEmailProperty::pluck($data);
            if($email){
                $name = $email;
            }
        }
        if(empty($name)){
            $name = BaseClientIdProperty::pluck($data) . '-'. UserProviderIdProperty::pluck($data);
        }
        if(empty($name)){
            le("No name from this profile: ".\App\Logging\QMLog::print_r($data, true));
        }
        return BaseUserLoginProperty::sanitize($name);
    }
	/**
	 * @param array $data
	 * @return array
	 */
    public static function checkUserNameInNewUserArray(array $data): array{
        if(empty($data[User::FIELD_USER_LOGIN])){
            QMLog::error("No user_login field for new user! new user: ".json_encode($data));
            if(isset($data[User::FIELD_USER_NICENAME])){
                $data[User::FIELD_USER_LOGIN] = $data[User::FIELD_USER_NICENAME];
            }
        }
        if(stripos($data[User::FIELD_USER_LOGIN], 'anonymous') === false){
            unset($data[User::FIELD_ID]);
        }
        // USER_NICENAME equal USER_LOGIN or breaks BuddyPress
        //$data[User::FIELD_USER_NICENAME] = $data[User::FIELD_USER_LOGIN];
        // Sanitization should be done before calling so it's only done for automatic use creation and exception is
        // thrown if user is manually registering so they can modify their name if necessary
        //$data[self::FIELD_USER_LOGIN] = self::sanitizeLoginName($data[self::FIELD_USER_LOGIN]);
        return $data;
    }
	/**
	 * @return void
	 * @throws \App\Exceptions\InvalidAttributeException
	 * @throws \App\Exceptions\RedundantVariableParameterException
	 */
	public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
    }
    /**
     * @param string $raw
     * @return string
     */
    public static function sanitize($raw): string{
//	    $cleaned = str_replace([
//            '@gmail.com',
//            '@yahoo.com',
//            '@hotmail.com'
//	                           ],
//	                           '',
//	                           $raw);
        $cleaned = QMStr::removeUrlUnsafeCharacters($raw, true);
        return $cleaned;
    }
}
