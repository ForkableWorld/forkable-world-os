<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\User;
use App\Exceptions\BadRequestException;
use App\Models\User;
use App\Traits\PropertyTraits\UserProperty;
use App\Properties\Base\BasePhoneVerificationCodeProperty;
use App\Utils\Env;
use LogicException;
use App\Slim\Model\User\QMUser;
use Twilio\Rest\Client;
class UserPhoneVerificationCodeProperty extends BasePhoneVerificationCodeProperty
{
	use UserProperty;
	public $table = User::TABLE;
	public $parentClass = User::class;

	/**
	 * @param QMUser $user
	 * @param string $phoneVerificationCode
	 * @return bool
	 * @throws \Twilio\Exceptions\ConfigurationException
	 * @throws \Twilio\Exceptions\TwilioException
	 */
	public static function setPhoneVerificationCode($user, $phoneVerificationCode)
	{
		if ($phoneVerificationCode === null) {
			throw new LogicException('$phoneVerificationCode not provided to setPhoneVerificationCode');
		}
		if ($user === null) {
			throw new LogicException('$user not provided to setPhoneVerificationCode');
		}
		if (strlen($phoneVerificationCode) !== 6) {
			throw new BadRequestException('phoneVerificationCode must be 6 characters');
		}
		if ($phoneVerificationCode !== $user->phoneVerificationCode) {
			throw new BadRequestException('phoneVerificationCode does not match.  Please try again or resubmit phone number to get a new code');
		}
		$user->updateDbRow(['phone_verification_code' => null,]);
		// Your Account SID and Auth Token from twilio.com/console
		$sid = Env::get('TWILIO_ACCOUNT_SID');
		$token = Env::get('TWILIO_AUTH_TOKEN');
		$client = new Client($sid, $token);
		$qmPhoneNumber = Env::get('TWILIO_PHONE_NUMBER');
		// Use the client to do fun stuff like send text messages!
		$client->messages->create(// the number you'd like to send the message to
			$user->phoneNumber, [
			// A Twilio phone number you purchased at twilio.com/console
			'from' => $qmPhoneNumber,
			// the body of the text message you'd like to send
			'body' => 'Congratulations!  Your number is now verified!'
		]);
		return true;
	}
}
