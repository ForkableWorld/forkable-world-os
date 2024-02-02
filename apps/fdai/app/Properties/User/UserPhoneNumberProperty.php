<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\User;
use App\Exceptions\BadRequestException;
use App\Models\User;
use App\Traits\PropertyTraits\UserProperty;
use App\Properties\Base\BasePhoneNumberProperty;
use App\Utils\Env;
use LogicException;
use App\Slim\Model\User\QMUser;
use Twilio\Rest\Client;
class UserPhoneNumberProperty extends BasePhoneNumberProperty
{
    use UserProperty;
    public $table = User::TABLE;
    public $parentClass = User::class;

    /**
     * @param QMUser $user
     * @param string $phoneNumber
     * @return bool
     * @throws \Twilio\Exceptions\ConfigurationException
     * @throws \Twilio\Exceptions\TwilioException
     */
    public static function setPhoneNumber($user, $phoneNumber)
    {
        if ($phoneNumber === null) {
            throw new LogicException('$phoneNumber not provided to setPhoneNumber');
        }
        if ($user === null) {
            throw new LogicException('$user not provided to setPhoneNumber');
        }
        if (strlen($phoneNumber) < 8) {
            throw new BadRequestException('phoneNumber cannot be less than 8 characters');
        }
        if (strlen($phoneNumber) > 15) {
            throw new BadRequestException('phoneNumber cannot be greater than 15 characters');
        }
        $phoneVerificationCode = QMUser::generateRandomString(6);
        $user->updateDbRow([
            'phone_number' => $phoneNumber,
            'phone_verification_code' => $phoneVerificationCode,
        ]);
        // Your Account SID and Auth Token from twilio.com/console
        $client = new Client(Env::getRequired('TWILIO_SID'), Env::getRequired('TWILIO_TOKEN'));
        $qmPhoneNumber = '+1 863-546-4264';
        // Use the client to do fun stuff like send text messages!
        $client->messages->create(// the number you'd like to send the message to
            $phoneNumber, [
            // A Twilio phone number you purchased at twilio.com/console
            'from' => $qmPhoneNumber,
            // the body of the text message you'd like to send
            'body' => 'Please enter this verification code in the QuantiModo app or website: ' . $phoneVerificationCode
        ]);
        return true;
    }
}
