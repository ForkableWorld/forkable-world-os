<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Http\Controllers;
use App\Exceptions\BadRequestException;
use App\Exceptions\ClientNotFoundException;
use App\Exceptions\InvalidEmailException;
use App\Exceptions\NoEmailAddressException;
use App\Files\MimeContentTypeHelper;
use App\Logging\QMLog;
use App\Mail\AndroidBuildEmail;
use App\Mail\ChromeExtensionEmail;
use App\Mail\CouponEmail;
use App\Mail\DefaultEmail;
use App\Mail\FitbitEmail;
use App\Mail\QMSendgrid;
use App\Mail\TooManyEmailsException;
use App\Mail\TrackingReminderNotificationEmail;
use App\Models\SentEmail;
use App\Models\User;
use App\Slim\View\Request\QMRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
/** Class EmailController
 * @package App\Http\Controllers
 */
class EmailController extends Controller {
	/**
	 * @return JsonResponse
	 * @throws ClientNotFoundException
	 * @throws TooManyEmailsException
	 * @throws InvalidEmailException
	 * @throws NoEmailAddressException
	 */
	public function postEmail(): JsonResponse{
		/** @var User $user */
		$user = $body['user'] = Auth::user();
		$body = $this->getRequest()->all();
		$type = QMSendgrid::formatSentEmailType($body['emailType']);
		$this->checkIfInAllowedSentEmailTypes($type);
		$address = $body['emailAddress'] ?? $user->user_email;
		if($type === QMSendgrid::SENT_EMAIL_TYPE_TRACKING_REMINDER_NOTIFICATIONS){
			$sent = [TrackingReminderNotificationEmail::sendIt($address, $body)];
		} elseif($type === QMSendgrid::SENT_EMAIL_TYPE_COUPON_INSTRUCTIONS){
			$sent = [CouponEmail::sendIt($address, $body)];
		} elseif($type === QMSendgrid::SENT_EMAIL_TYPE_FITBIT){
			$sent = [FitbitEmail::sendIt($address, $body)];
		} elseif($type === QMSendgrid::SENT_EMAIL_TYPE_CHROME){
			$sent = [ChromeExtensionEmail::sendIt($address, $body)];
		} elseif($type === QMSendgrid::SENT_EMAIL_TYPE_PHYSICIAN_INVITATION){
			$sent = $user->getQMUser()->shareData($body['doctorEmail'], $body['doctorName']);
		} elseif($type === QMSendgrid::SENT_EMAIL_TYPE_ANDROID_BUILD_READY){
			$sent = AndroidBuildEmail::sendAndroidBuildNotificationEmail($body['clientId']);
		} else{
			$mail = new DefaultEmail($address);
			$sent = [$mail->sendMe()];
		}
		return new JsonResponse([
			'success' => true,
			SentEmail::TABLE => $sent,
		]);
	}
	public function emailPreview(): \Illuminate\Http\Response{
		$ns = 'App\Mail';
		$class = QMRequest::getParam('class');
		if(stripos($class, $ns) === false){
			$class = $ns . '\\' . $class;
		}
		/** @var QMSendgrid $class */
		/** @var QMSendgrid $email */
		$email = $class::test();
		return Response::make($email->getHtmlContent(), 200, [
			'Content-Type' => MimeContentTypeHelper::HTML,
		]);
	}
	/**
	 * @param $sentEmailType
	 * @throws BadRequestException
	 */
	public function checkIfInAllowedSentEmailTypes(string $sentEmailType){
		$allowedTypes = QMSendgrid::getAllowedSentEmailTypes();
		if(!in_array($sentEmailType, $allowedTypes, true)){
			$name = "Wrong Email Type: " . $sentEmailType;
			$message = 'Not in allowed email types: ' . implode(', ', $allowedTypes);
			QMLog::error($name, [], false, $message);
			throw new BadRequestException($message);
		}
	}
	public function sendTest(){
		$u = User::mike();
		$u->sendPasswordResetNotification($token = 'token');
		$email = $u->email;
		return \response()->json([
			'success' => true,
			'email' => $email,
			'message' => 'Sent to ' . $email,
		]);
	}
}
