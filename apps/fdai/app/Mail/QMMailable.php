<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Mail;
use App\Models\SentEmail;
use App\Models\User;
use App\Storage\DB\QMQB;
use App\Traits\HasClassName;
use App\UI\HtmlHelper;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
abstract class QMMailable extends Mailable {
	use Queueable, SerializesModels, HasClassName;
	public const MINIMUM_SECONDS_BETWEEN_EMAILS = 86400;
	protected $params;
	public $recipientAddress;
	public $unsubscribeLink;
	/**
	 * @var User
	 */
	protected $user;
	/**
	 * Create a new message instance.
	 * @param string $address
	 * @param array $params
	 * @throws TooManyEmailsException
	 */
	public function __construct(string $address, array $params = []){
		$this->params = $params;
		$this->setRecipientAddress($address);
	}
	/**
	 * @return SentEmail
	 */
	public function sendMe(): SentEmail{
		$mailer = Mail::to((object)['email' => $this->getRecipientAddress(), 'name' => $this->getRecipientName()]);
		$mailer->bcc((object)['email' => "sent-emails@quantimo.do", 'name' => "Sent Emails Archive"]);
		$mailer->send($this);
		try {
			$content = $this->render();
		} catch (\ReflectionException $e) {
			/** @var \LogicException $e */
			throw $e;
		}
		$failures = Mail::failures();
		if($failures){
			le('$failures', $failures);
		}
		$data = [
			SentEmail::FIELD_TYPE => static::getType(),
			SentEmail::FIELD_USER_ID => $this->getUserId(),
			SentEmail::FIELD_EMAIL_ADDRESS => $this->getRecipientAddress(),
			SentEmail::FIELD_CONTENT => $content,
			SentEmail::FIELD_RESPONSE => null,
			SentEmail::FIELD_SUBJECT => $this->subject,
		];
		$sent = SentEmail::updateUserLastEmailedAtAndCreate($data);
		return $sent;
	}
	/**
	 * @param string $address
	 * @param array $params
	 * @return SentEmail
	 * @throws TooManyEmailsException
	 */
	public static function sendIt(string $address, array $params = []): SentEmail{
		$mail = new static($address, $params);
		$sentEmail = $mail->sendMe();
		return $sentEmail;
	}
	/**
	 * @param string $userEmail
	 * @return string
	 */
	public static function getUnsubscribeLink(string $userEmail): string{
		return 'https://web.quantimo.do/#/app/notificationPreferences?userEmail=' . $userEmail;
	}
	public static function getType(): string{
		return (new \ReflectionClass(static::class))->getShortName();
	}
	protected function getParams(): array{
		$arr = $this->params;
		foreach($this as $key => $value){
			if(!isset($arr[$key])){
				$arr[$key] = $value;
			}
		}
		return $arr;
	}
	/**
	 * @throws TooManyEmailsException
	 */
	protected function checkLastSentTime(){
		$lastAt = $this->getLastSentAt();
		if(!$lastAt){
			return;
		}
		$min = static::MINIMUM_SECONDS_BETWEEN_EMAILS;
		$since = time() - strtotime($lastAt);
		if($since < $min){
			throw new TooManyEmailsException($this->getRecipientAddress(), static::getType(), $lastAt);
		}
	}
	protected function getRecipientAddress(): string{
		return $this->recipientAddress;
	}
	/**
	 * @param string $address
	 * @throws TooManyEmailsException
	 */
	public function setRecipientAddress(string $address){
		$this->recipientAddress = $address;
		$this->unsubscribeLink = self::getUnsubscribeLink($address);
		$this->checkLastSentTime();
	}
	public function getLastSentAt(): ?string{
		$mail =
			SentEmail::whereEmailAddress($this->getRecipientAddress())->where(SentEmail::FIELD_TYPE, static::getType())
				->max(SentEmail::CREATED_AT);
		return $mail;
	}
	/**
	 * @return array
	 */
	public static function getRecentlyEmailedUserIds(): array{
		$userIds = SentEmail::whereType(static::getType())
//            ->whereRaw('created_at > NOW() - INTERVAL ' . static::MINIMUM_SECONDS_BETWEEN_EMAILS . ' SECOND')
            ->where('created_at', '>', Carbon::now()
                ->subtract(CarbonInterval::seconds(static::MINIMUM_SECONDS_BETWEEN_EMAILS)))
            ->pluck(SentEmail::FIELD_USER_ID);
		return $userIds->all();
	}
	/**
	 * @return User
	 */
	public function getUser(): ?User{
		$u = $this->user;
		if($u){
			return $u;
		}
		if($u === false){
			return null;
		}
		$u = User::findByEmail($this->getRecipientAddress());
		if(!$u){
			$this->user = false;
			return null;
		}
		return $this->user = $u;
	}
	/**
	 * @param array $params
	 * @return array
	 * @throws TooManyEmailsException
	 */
	public static function sendToAll(array $params = []): array{
		$qb = static::usersQB();
		$emails = $qb->pluck(User::FIELD_USER_EMAIL);
		if(!count($emails)){
			return [];
		}
		$responses = [];
		foreach($emails as $email){
			$responses[] = static::sendIt($email, $params);
		}
		return $responses;
	}
	/**
	 * @return Builder
	 */
	public static function usersQB(): Builder{
		$qb = User::query();
		$excluded = static::getRecentlyEmailedUserIds();
		$qb->whereNotNull(User::FIELD_USER_EMAIL);
		$qb->whereNotIn(User::FIELD_ID, $excluded);
		$qb->where(User::FIELD_UNSUBSCRIBED, false);
        QMQB::notLike($qb, User::FIELD_USER_EMAIL, '%deleted_%');
		return $qb;
	}
	private function getRecipientName(): ?string{
		if($u = $this->getUser()){
			return $u->display_name;
		}
		return null;
	}
	private function getUserId(): ?int{
		if($u = $this->getUser()){
			return $u->getId();
		}
		return null;
	}
	/**
	 * @param string $html
	 * @param string $title
	 * @param $obj
	 * @return string
	 */
	public static function addHeadIfNecessary(string $html, string $title, $obj): string{
		if(strpos($html, "<head>") === false){
			$body = $html;
			if(stripos($html, "</body>") !== false){
				$body = HtmlHelper::getBody($html);
			}
			try {
				$v = view('email.email-layout', [
					'title' => $title,
					'content' => $body,
					'obj' => $obj,
				]);
				$html = HtmlHelper::renderView($v);
			} catch (\Throwable $e) {
				le($e);
			}
		}
		return $html;
	}
}
