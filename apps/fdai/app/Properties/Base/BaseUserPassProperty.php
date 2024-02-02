<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\User;
use App\Properties\User\UserPasswordProperty;
use App\Traits\PropertyTraits\IsString;
use App\Exceptions\BadRequestException;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use App\Types\QMArr;
use Hautelook\Phpass\PasswordHash;
use Illuminate\Support\Facades\Hash;
use App\Slim\Model\User\QMUser;
use OpenApi\Generator;
class BaseUserPassProperty extends BasePasswordProperty {

	public $dbInput = 'string,255:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = 'Hash of the user’s password.';
	public $example = '$P$BPnTukcht.ttroNxdlVs7Dn11rlOzQ0';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::OAUTH_ACCESS_TOKEN;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::COLLABORATOR;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 255;
	public $minLength = 6;
	public $name = self::NAME;
	public const NAME = 'user_pass';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|min:6|max:255';
	public $title = 'User Pass';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|min:6|max:255';
	public static function pluckEncrypted(array $data): string{
		return static::validateAndHashNewPassword($data);
	}
    /**
     * @param array $data
     * @return string
     */
    public static function validateAndHashNewPassword(array $data): string {
        $plainText = self::pluck($data);
        $confirmation = QMArr::getValue($data, [
            'pwdConfirm',
            'user_pass_confirmation',
        ]);
        if ($confirmation && $plainText !== $confirmation) {
            throw new BadRequestException("Password and confirmation do not match! ");
        }
        if (!$plainText) {
            throw new BadRequestException("Password required to create new user! ");
        }
        return self::hashPassword($plainText);
    }
    /**
     * @param $data
     * @return string
     */
    public static function pluckOrDefault($data): string{
        return self::pluck($data) ?: self::generate();
    }
	public static function generate():string{
		return QMUser::generateRandomString();
	}
    public static function pluckAndCheck(array $input, User $user): bool{
	    $plainText = UserPasswordProperty::pluck($input);
	    $hashed = $user->getHashedPassword();
	    return self::check($plainText, $hashed);
    }
    public static function hashPassword(string $plainText): string {
        return Hash::make($plainText);
    }
	/**
	 * @param string $plainText
	 * @param string $hashed
	 * @return bool
	 */
	public static function check(string $plainText, string $hashed): bool{
		$valid = Hash::check($plainText, $hashed);
		if(!$valid){ // Password might still be using WordPress hashing
			$wpHash = new PasswordHash(8, true);
			$valid = $wpHash->CheckPassword($plainText, $hashed);
		}
		if(!$valid){ // Password might still be using old hashing
			throw new BadRequestException("Password is not valid! ");
		}
		return $valid;
	}
}
