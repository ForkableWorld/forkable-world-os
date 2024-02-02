<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Http\Urls\IntendedUrl;
use App\Models\User;
use App\Properties\BaseProperty;
use App\Slim\Model\User\QMUser;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\Fields\Field;
use App\Fields\Text;
use OpenApi\Generator;
class BaseUserEmailProperty extends BaseProperty{
	use IsString;
    public const SYNONYMS = [
        User::FIELD_USER_EMAIL,
        'email',
        'email_address',
	    'PrimaryEmail'
    ];
	const TEST_EMAIL = 'staging-test-user@quantimo.do';
	public $dbInput = 'string,100:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = 'Email address of the user.';
	public $example = 'zero@quantimodo.com';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::LAST_EMAIL;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::EMAIL;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 100;
	public $minLength = 3;
	public $name = self::NAME;
	public const NAME = 'user_email';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|min:3|max:100';
	public $title = 'User Email';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|min:3|max:100';
	/**
	 * @return string|null
	 */
	public static function getUserEmailFromUrlOrRedirectParameter(): ?string{
		if(IntendedUrl::getParam('user.email')){
			return IntendedUrl::getParam('user.email');
		}
		if(IntendedUrl::getParam('user_email')){
			return IntendedUrl::getParam('user_email');
		}
		return null;
	}
	/**
     * @param array|object $data
     * @return string
     */
    public static function pluck($data): ?string{
        if (!is_array($data)) {
            $data = json_decode(json_encode($data), true);
        }
        $email = parent::pluck($data);
        if (!$email && isset($data['emails'][0]['value'])) {
            $email = $data['emails'][0]['value'];
        }
        if (!$email && isset($data['log']) && strpos($data['log'], '@')) {
            $email = $data['log'];
        }
        return $email;
    }
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return \App\Fields\Field
	 */
	public function getField($resolveCallback = null, string $name = null): Field{
        return Text::make('Email', User::FIELD_USER_EMAIL)
            ->sortable()
            ->hideFromIndex()
            ->rules('required', 'email', 'max:254')
            ->creationRules('unique:'.User::TABLE.','.User::FIELD_USER_EMAIL)
            ->updateRules('unique:'.User::TABLE.','.User::FIELD_USER_EMAIL.',{{resourceId}}');
    }
}
