<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Mail;
use App\AppSettings\AppSettings;
use App\AppSettings\HostAppSettings;
use App\Buttons\States\NotificationPreferencesStateButton;
use App\Computers\ThisComputer;
use App\DevOps\XDebug;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\InvalidEmailException;
use App\Exceptions\InvalidStringException;
use App\Exceptions\NoEmailAddressException;
use App\Files\FileHelper;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Models\SentEmail;
use App\Properties\SentEmail\SentEmailSubjectProperty;
use App\Properties\User\UserIdProperty;
use App\Reports\AnalyticalReport;
use App\Slim\Model\Slack\SlackAttachment;
use App\Slim\Model\Slack\SlackMessage;
use App\Slim\Model\StaticModel;
use App\Slim\Model\User\QMUser;
use App\Storage\S3\S3Private;
use App\Traits\HasClassName;
use App\Traits\LoggerTrait;
use App\Types\QMArr;
use App\Types\QMStr;
use App\UI\CssHelper;
use App\UI\HtmlHelper;
use App\Utils\AppMode;
use App\Utils\Env;
use App\Utils\EnvOverride;
use DrewM\MailChimp\MailChimp;
use Exception;
use ReflectionClass;
use RuntimeException;
use SendGrid;
use SendGrid\Mail\Content;
use SendGrid\Mail\Mail;
use SendGrid\Mail\MimeType;
use SendGrid\Response;
use Throwable;
class QMSendgrid extends Mail {
	use LoggerTrait, HasClassName;
	protected $tooSoon;
	/** @var $contents Content[] Content(s) of the email */
	protected $contents;
	public $date;
	public $subject;
	public $htmlContent;
	public const MINIMUM_SECONDS_BETWEEN_EMAILS = 86400;
	public $response;
	public $report;
	public $recipientEmailAddress;
	public $summaryResponse;
	public $user;
	public $userId;
	public const SENT_EMAIL_TYPE_ANDROID_BUILD_READY = 'android-build-ready';
	public const SENT_EMAIL_TYPE_CHROME = 'chrome';
	public const SENT_EMAIL_TYPE_COLLABORATOR_INVITATION = 'collaborator-invitation';
	public const SENT_EMAIL_TYPE_COUPON_INSTRUCTIONS = 'coupon-instructions';
	public const SENT_EMAIL_TYPE_FITBIT = 'fitbit';
	public const SENT_EMAIL_TYPE_MEASUREMENT_EXPORT = 'measurement-export';
	public const SENT_EMAIL_TYPE_PATIENT_INVITATION = 'patient-invitation';
	public const SENT_EMAIL_TYPE_PHYSICIAN_INVITATION = 'patient-authorization';
	public const SENT_EMAIL_TYPE_TRACKING_REMINDER_NOTIFICATIONS = 'tracking-reminder-notifications';
	/** @var string */
	protected $thirdPartyDisclaimer;
	protected $attachmentDownloadUrls = [];
	protected $sourceObject;
	/**
	 * Email constructor.
	 * @param int|null $recipientUserId
	 * @param string|null $subject
	 * @param string|null $htmlContent
	 */
	public function __construct(int $recipientUserId = null, string $subject = null, string $htmlContent = null,
		$sourceObject = null){
		if($sourceObject){$this->sourceObject = $sourceObject;}
		if($subject){$this->subject = $subject;}
		if($htmlContent){
			$this->setHtmlContent($htmlContent);
		}
		try {
			parent::__construct();
		} catch (SendGrid\Mail\TypeException $e) {
			le($e);
		}
		$this->setFromAppSettings();
		if(!$recipientUserId && !$subject && !$htmlContent){
			return;
		}
		try {  // Need to catch in case we try to send to physicians anyway
			$this->setRecipientAndEmailFromUserId($recipientUserId);
		} catch (InvalidEmailException | NoEmailAddressException $e) {
			$this->logError(__METHOD__.": ".$e->getMessage());
		}
	}
	private function setFromAppSettings(){
		$email = HostAppSettings::instance()->getAdditionalSettings()->companyEmail;
		try {
			$this->setFrom($email, HostAppSettings::instance()->appDisplayName);
		} catch (SendGrid\Mail\TypeException $e) {
			le($e);
		}
	}
	/**
	 * @param $userId
	 * @throws InvalidEmailException
	 * @throws NoEmailAddressException
	 */
	public function setRecipientAndEmailFromUserId(int $userId): void{
		if($userId){
			$this->userId = $userId;
			$u = $this->getRecipientUser();
			$address = $u->getEmail();
			$this->setRecipientEmailAddress($address);
		}
	}
	/**
	 * @return \DrewM\MailChimp\MailChimp
	 */
	private static function mailchimp(): MailChimp{
		try {
			return new MailChimp(Env::getRequired('MAILCHIMP_API_KEY'));
		} catch (Exception $e) {
			le($e);
		}
	}
	/**
	 * @param Content|string $type
	 * @param null $value
	 */
	public function addContent($type, $value = null){
		//QMValidatingTrait::assertStringDoesNotContain($value, ['class='], "email-content.html");
		if($contents = $this->getContents()){
			$this->contents = collect($contents)->filter(function($one) use ($type){
				/** @var Content $one */
				return $one->getType() !== $type;
			})->all();
		}
		try {
			parent::addContent($type, $value);
		} catch (SendGrid\Mail\TypeException $e) {
			le($e);
		}
	}
	/**
	 * @param string $userEmail
	 * @return string
	 */
	public static function getUnsubscribeFooter(string $userEmail): string{
		return HtmlHelper::renderView(view('email.email-physical-address-footer', [
			'userEmail' => $userEmail,
			'unsubscribeLink' => NotificationPreferencesStateButton::url(['userEmail' => $userEmail]),
		]));
	}
	/**
	 * @return int
	 */
	public static function getMinimumSecondsBetweenEmails(): int{
		return static::MINIMUM_SECONDS_BETWEEN_EMAILS;
	}
	/**
	 * @return bool
	 * @throws TooManyEmailsException
	 */
	protected function exceptionIfTooSoonToSendAnotherEmail(): bool{
		if($this->tooSoon === false){
			return false;
		}
		$min = $this->getMinimumSecondsBetweenEmails();
		if(!$min){
			return $this->tooSoon = false;
		}
		$address = $this->getRecipientEmailAddress();
		$lastEmailAt = self::getLastEmailedAt($address, $this->getType());
		if(!$lastEmailAt){
			return $this->tooSoon = false;
		}
		$secondsSince = time() - strtotime($lastEmailAt);
		if(AppMode::isTestingOrStaging()){
			if(stripos($address, '@quantimo.do') !== false || $address === "m@thinkbynumbers.org"){
				return $this->tooSoon = false;
			}
		}
		if($secondsSince < $min){
			throw new TooManyEmailsException($this->getRecipientEmailAddress(), $this->getType(), $lastEmailAt);
		}
		return $this->tooSoon = false;
	}
	/**
	 * @throws EmailsDisabledException
	 */
	private function exceptionIfEmailsDisabled(){
		$user = $this->getRecipientUser();
		if(!$user){
			return;
		}
		if($user->unsubscribedFromEmails()){
			throw new EmailsDisabledException("$user is not subscribed to emails so can't send $this");
		}
	}
	/**
	 * @return mixed
	 */
	public function __toString(){
		return "subject: " . $this->subject . " recipientEmailAddress: " . $this->recipientEmailAddress;
	}
	/**
	 * @param string $email
	 * @return bool
	 */
	public static function emailIsBlackListed($email): bool{
		// Comment these on 10/1/2017 when we switch production back from staging sendgrid account
		$blackList = [
			'q@quantimo.do',
			'm@sadfsa.do',
			'no@quantimo.do',
			'test@quantimo.do',
		];
		return in_array($email, $blackList);
	}
	/**
	 * @param string $path
	 * @param string|null $content_id
	 * @param string|null $downloadUrl
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function addAttachmentByFilePath(string $path, string $content_id = null, string $downloadUrl = null){
		//$ext = StringHelper::getStringAfterLastSubString($path, '.');
		$mimeContentType = FileHelper::guessMimeContentTypeBasedOnFileContents($path);
		$filename = FileHelper::getFileNameFromPath($path);
		if($downloadUrl){
			$this->attachmentDownloadUrls[$filename] = $downloadUrl;
		}
		if(!$content_id){
			$content_id = QMStr::between($path, '/', '.');
		}
		/** @noinspection PhpUnhandledExceptionInspection */
		$this->addAttachment(base64_encode(file_get_contents($path)), $mimeContentType, $filename, "attachment",
			$content_id);
	}
	/**
	 * @return string
	 */
	public function getRecipientEmailAddress(): string{
		if(Env::get('DEBUG_EMAIL')){
			return Env::get('DEBUG_EMAIL');
		}
		if($this->recipientEmailAddress){
			return $this->recipientEmailAddress;
		}
		$u = $this->getRecipientUser();
		return $u->email;
	}
	/**
	 * @return SendGrid
	 */
	private function getSendGrid(): SendGrid{
		$sendGrid = new SendGrid(Env::get('SENDGRID_API_KEY'));
		//$sendGrid->apiKey = \App\Utils\Env::get('SENDGRID_PASSWORD');
		//$sendGrid->apiUser = \App\Utils\Env::get('SENDGRID_USERNAME');
		return $sendGrid;
	}
	/**
	 * @param SendGrid\Mail\Cc|string $bcc
	 * @param null $name
	 * @param null $substitutions
	 * @param null $personalizationIndex
	 * @param null $personalization
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function addBcc($bcc, $name = null, $substitutions = null, $personalizationIndex = null,
		$personalization = null){
		$personalizations = $this->getPersonalizations();
		$existingBccs = $personalizations[0]->getBccs();
		$emailAddress = (is_string($bcc)) ? $bcc : $bcc->getEmailAddress();
		if($existingBccs){
			foreach($existingBccs as $existingBcc){
				if($emailAddress === $existingBcc->getEmailAddress()){
					ConsoleLog::info("Already added bcc for $bcc");
					return;
				}
			}
		}
		/** @noinspection PhpUnhandledExceptionInspection */
		parent::addBcc($bcc, $name, $substitutions, $personalizationIndex, $personalization);
	}
	/**
	 * @param SendGrid\Mail\Cc|string $cc
	 * @param null $name
	 * @param null $substitutions
	 * @param null $personalizationIndex
	 * @param null $personalization
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function addCc($cc, $name = null, $substitutions = null, $personalizationIndex = null,
		$personalization = null){
		$personalizations = $this->getPersonalizations();
		$existingCcs = $personalizations[0]->getBccs();
		$emailAddress = (is_string($cc)) ? $cc : $cc->getEmailAddress();
		if($existingCcs){
			foreach($existingCcs as $existingCc){
				if($emailAddress === $existingCc->getEmailAddress()){
					ConsoleLog::info("Already added cc for $cc");
					return;
				}
			}
		}
		/** @noinspection PhpUnhandledExceptionInspection */
		parent::addCc($cc, $name, $substitutions, $personalizationIndex, $personalization);
	}
	public function addTo($to, $name = null, $substitutions = null, $personalizationIndex = null,
		$personalization = null){
		$personalizations = $this->getPersonalizations();
		$existingTos = $personalizations[0]->getTos();
		$emailAddress = (is_string($to)) ? $to : $to->getEmailAddress();
		if($existingTos){
			foreach($existingTos as $existingTo){
				if($emailAddress === $existingTo->getEmailAddress()){
					ConsoleLog::info("Already added $to");
					return;
				}
			}
		}
		parent::addTo($to, $name, $substitutions, $personalizationIndex, $personalization);
	}
	/**
	 * @return Response
	 * @throws SendGrid\Mail\TypeException
	 * @throws TooManyEmailsException
	 */
	public function send(): Response{
		$this->exceptionIfTooSoonToSendAnotherEmail();
		$this->exceptionIfEmailsDisabled();
		$this->subject = $this->getOrGenerateEmailSubject();
		$this->setSubject($this->subject);
		$content = $this->getHtmlContent();
		$this->addContent(MimeType::HTML, $content);
		$address = $this->getRecipientEmailAddress();
		$this->addTo($address, $this->getRecipientName());
		$this->addBcc("sent-emails@quantimo.do", "Sent Emails Archive");
		$u = $this->getRecipientUser();
		$sendGrid = $this->getSendGrid();
		if($this->weShouldActuallySend($address)){
			$r = $this->response = $sendGrid->send($this);
			try {
				// Why is this necessary? $this->slack($r->statusCode());
			} catch (Throwable $e) {
				QMLog::throwIfNotProductionAPIRequest($e);
			}
			$code = $r->statusCode();
			$this->logInfo("$code response after sending to $address");
		} else{
			$code = 202;
			$r = $this->response = new Response(202);
			$this->logInfo("Not actually sending to $address because we're testing or something");
		}
		if($code === 400){
			throw new RuntimeException($this . ": Got response" . $r->body());
		}
		$res = $r->statusCode();
		if($r->body()){
			$res .= ": " . $r->body();
		}
		$data = [
			SentEmail::FIELD_TYPE => $this->getType(),
			SentEmail::FIELD_EMAIL_ADDRESS => $address,
			SentEmail::FIELD_CONTENT => $content,
			SentEmail::FIELD_RESPONSE => $res,
			SentEmail::FIELD_SUBJECT => $this->getOrGenerateEmailSubject(),
		];
		if($userId = ($u) ? $u->getId() : null){
			$data[SentEmail::FIELD_USER_ID] = $userId;
		}
		SentEmail::updateUserLastEmailedAtAndCreate($data);
		if(!empty($r->body())){
			$this->logInfo($r->body());
		}
		$this->date = date('Y-m-d H:i:s');
		if($code === 202){
			$success = true;
		} else{
			$success = false;
		}
		$r->success = $success;
		if(!$success){
			if(AppMode::isTestingOrStaging() || !AppMode::isApiRequest()){
				throw new RuntimeException(QMLog::print_r($r, true));
			}
		}
		$this->summaryResponse = [
			'subject' => $this->subject,
			'contents' => $this->getHtmlContent(),
			'success' => $success,
		];
		return $r;
	}
	/**
	 * @param string $address
	 * @return bool
	 */
	private function weShouldActuallySend(string $address): bool{
		if(XDebug::active()){
			return true;
		}
		if(AppMode::isTestingOrStaging() && !EnvOverride::isLocal()){
			$this->logInfo("Not sending email during testing to avoid exceeding sendgrid limits");
			return false;
		}
		if(self::emailIsBlackListed($address)){
			$this->logInfo($this->getRecipientEmailAddress() . " is blacklisted to avoid exceeding sendgrid limits");
			return false;
		}
		if(AppMode::isTestingOrStaging() && stripos($address, '@thinkbynumbers.org') === false &&
			stripos($address, '@quantimo.do') === false){
			ConsoleLog::info("Can't email non-qm emails during testing!");
			return false;
		}
		return true;
	}
	/**
	 * @param $email
	 * @param $list_id
	 * @throws \Exception
	 */
	public static function deleteUserFromMailChimpList($email, $list_id){
		$MailChimp = self::mailchimp();
		$subscriber_hash = $MailChimp->subscriberHash($email);
		$MailChimp->delete("lists/$list_id/members/$subscriber_hash");
		if(!$MailChimp->success() && AppMode::isProduction()){
			QMLog::error($MailChimp->getLastError());
		}
	}
	/**
	 * @param $user
	 * @param $list_id
	 * @return bool
	 * @throws Exception
	 * @internal param $mergeFields
	 */
	public static function addUserToMailChimpList($user, $list_id): bool{
		$MailChimp = self::mailchimp();
		$postArray = [
			'email_address' => $user->email,
			'status' => 'subscribed',
		];
		if($user->firstName){
			$mergeFields['FNAME'] = $user->firstName;
		}
		if($user->lastName){
			$mergeFields['LNAME'] = $user->lastName;
		}
		if(isset($mergeFields)){
			$postArray['merge_fields'] = $mergeFields;
		}
		$result = $MailChimp->post("lists/$list_id/members", $postArray);
		if($MailChimp->success()){
			return true;
		}
		QMLog::error($MailChimp->getLastError(), ['result' => $result]);
		return false;
	}
	/**
	 * @param array|string $body
	 * @return string
	 */
	public static function formatSentEmailType($body): string{
		if(is_string($body)){
			$type = $body;
		} else{
			$type = $body['emailType'] ?? $body['template'] ?? $body['emailCategory'] ?? null;
		}
		if(!$type){
			return static::getType();
		}
		$type = QMStr::convertStringFromCamelCaseToDashes($type);
		$type = str_replace('email-', '', $type);
		$type = str_replace('email.', '', $type);
		$type = str_replace('_', '-', $type);
		return $type;
	}
	/**
	 * @return array
	 */
	public static function getAllowedSentEmailTypes(): array{
		return QMArr::getValuesAsArray(self::getConstants());
	}
	/**
	 * @return QMUser
	 */
	public function getRecipientUser(): ?QMUser{
		if(!$this->userId){
			return null;
		}
		return QMUser::find($this->userId);
	}
	/**
	 * @param string $emailAddress
	 * @param int $minimumSecondsSinceLastEmail
	 * @param string|null $type
	 * @return bool
	 */
	public static function emailedInLast(string $emailAddress, int $minimumSecondsSinceLastEmail = 3600,
		string $type = null): bool{
		$lastEmailAt = self::getLastEmailedAt($emailAddress, $type);
		if(!$lastEmailAt){
			return false;
		}
		if(time() - strtotime($lastEmailAt) < $minimumSecondsSinceLastEmail){
			QMLog::error("Already emailed in last hour!");
			return true;
		}
		return false;
	}
	/**
	 * @param string $emailAddress
	 * @param string|null $type
	 * @return string
	 */
	public static function getLastEmailedAt(string $emailAddress, string $type = null): ?string{
		$lastEmail = self::getLastEmail($emailAddress, $type);
		if($lastEmail){
			return $lastEmail->created_at->toDateTimeString();
		}
		return null;
	}
	/**
	 * @param string $emailAddress
	 * @param string|null $type
	 * @return array|object|null
	 */
	public static function getLastEmail(string $emailAddress, string $type = null): ?SentEmail{
		$qb = SentEmail::whereEmailAddress($emailAddress);
		if($type){
			$qb->where(SentEmail::FIELD_TYPE, $type);
		}
		$qb = $qb->orderBy(SentEmail::CREATED_AT, 'desc');
		return $qb->first();
	}
	/**
	 * @return string
	 */
	public function getUnsubscribeLink(): string{
		return NotificationPreferencesStateButton::url(['userEmail' => $this->getRecipientEmailAddress()]);
	}
	/**
	 * @param string $htmlContent
	 * @return string
	 */
	public function setHtmlContent(string $htmlContent): string{
		if(stripos($htmlContent, 'class=') !== false){
			$htmlContent = CssHelper::inlineCss($htmlContent);
		}
		if(AppMode::isUnitOrStagingUnitTest()){
			try {
				HtmlHelper::validateHtml($htmlContent, static::getType());
			} catch (InvalidStringException $e) {
				le($e);
			}
		}
		if(empty($htmlContent)){
			le("Email content is empty!");
		}
		return $this->htmlContent = $htmlContent;
	}
	/**
	 * @return AnalyticalReport
	 */
	public function getReport(): ?AnalyticalReport{
		return $this->report;
	}
	/**
	 * @param AnalyticalReport $report
	 */
	public function setReport(AnalyticalReport $report): void{
		$this->report = $report;
	}
	/**
	 * @param $html
	 * @return string|string[]
	 */
	public function addUnsubscribeFooterIfNecessary(string $html): string{
		if(stripos($html, 'unsubscribe') === false){
			$footer = self::getUnsubscribeFooter($this->getRecipientEmailAddress());
			$html = HtmlHelper::addToEndOrBeforeClosingBodyTag($html, $footer);
		}
		return $html;
	}
	/**
	 * @param BaseModel|StaticModel $sourceObject
	 */
	public function setSourceObject($sourceObject): void{
		$this->sourceObject = $sourceObject;
	}
	/**
	 * @return AppSettings
	 */
	public function getHostAppSettings(): AppSettings{
		return HostAppSettings::instance();
	}
	/**
	 * @return string
	 */
	protected function getAppDisplayName(): string{
		return $this->getHostAppSettings()->getTitleAttribute();
	}
	/**
	 * @return string
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function getHtmlContent(): string{
		$params = $this->getOrSetHtmlOrTemplateParams();
		if(is_array($params)){
			if(!isset($params['unsubscribeLink'])){
				$params['unsubscribeLink'] = $this->getUnsubscribeLink();
			}
			$params['emailType'] = self::formatSentEmailType($params);
			$params['obj'] = $this->getSourceObject();
			/** @noinspection PhpUnhandledExceptionInspection */
			$html = view('email.default-email', $params)->render();
		} else{
			$html = $params;
		}
		if(AppMode::isUnitOrStagingUnitTest()){
			/** @noinspection PhpUnhandledExceptionInspection */
			HtmlHelper::validateHtml($html, static::getType());
		}
		$html = $this->addUnsubscribeFooterIfNecessary($html);
		$html = QMMailable::addHeadIfNecessary($html, $this->getOrGenerateEmailSubject(), $this->getSourceObject());
		if(AppMode::isTestingOrStaging() || $this->recipientIsMikeOrQuantiModo()){
			$html = ThisComputer::addServerBranchJobTestNameDebugHtml($html);
		}
		$css = CssHelper::getEmailCssStyleTag();
		if(strpos($html, $css) === false){
			$html = $css . "\n" . $html;
		}
		return $this->htmlContent = $html;
	}
	/**
	 * @return array|string
	 */
	public function getOrSetHtmlOrTemplateParams(){
		return $this->htmlContent;
	}
	/**
	 * @return string
	 */
	public function getOrGenerateEmailSubject(): string{
		$subject = $this->subject;
		return $this->subject = QMStr::truncate($subject, SentEmailSubjectProperty::MAX_SUBJECT_LENGTH, '...');
	}
	/**
	 * @return string
	 */
	public static function getType(): string{
		return (new ReflectionClass(static::class))->getShortName();
	}
	/**
	 * @return Response
	 */
	public function getResponse(): Response{
		return $this->response;
	}
	/**
	 * @param string $recipientEmailAddress
	 * @throws InvalidEmailException
	 */
	public function setRecipientEmailAddress(string $recipientEmailAddress): void{
		if(!filter_var($recipientEmailAddress, FILTER_VALIDATE_EMAIL)){
			throw new InvalidEmailException("Invalid recipientEmailAddress: $recipientEmailAddress");
		}
		$this->recipientEmailAddress = $recipientEmailAddress;
	}
	/**
	 * @param QMUser $thirdPartyUser
	 * @param QMUser $originalUser
	 * @throws InvalidEmailException
	 * @throws NoEmailAddressException
	 * @throws SendGrid\Mail\TypeException
	 * @throws TooManyEmailsException
	 */
	public function sendToThirdParty(QMUser $thirdPartyUser, QMUser $originalUser){
		$htmlContent = QMStr::convertTextToThirdParty($this->htmlContent, $originalUser);
		$subject = QMStr::convertTextToThirdParty($this->subject, $originalUser);
		$m = new static($thirdPartyUser->getId(), $subject, $htmlContent);
		$this->setRecipientAndEmailFromUserId($thirdPartyUser->getId());
		$m->thirdPartyDisclaimer =
			"<h2>You are receiving this email because $originalUser->displayName has shared their " . 'data with you. ';
		$m->send();
	}
	/**
	 * @return string
	 */
	private function getRecipientName(): string{
		$u = $this->getRecipientUser();
		if($u){
			return $u->getDisplayNameAttribute();
		}
		$email = $this->getRecipientEmailAddress();
		return QMStr::before('@', $email);
	}
	/**
	 * @param SendGrid\Mail\Subject|string $subject
	 * @param null $personalizationIndex
	 * @param null $personalization
	 * @return string
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function setSubject($subject, $personalizationIndex = null, $personalization = null){
		$subject = QMStr::truncate($subject, SentEmailSubjectProperty::MAX_SUBJECT_LENGTH, '...');
		$this->subject = $subject;
		/** @noinspection PhpUnhandledExceptionInspection */
		parent::setSubject($subject, $personalizationIndex, $personalization);
		return $subject;
	}
	/**
	 * @param int $responseCode
	 * @return SlackMessage
	 */
	public function slack(int $responseCode): SlackMessage{
		$m = new SlackMessage((new ReflectionClass(static::class))->getShortName());
		$text = "Subject: " . $this->getOrGenerateEmailSubject() . "\n";
		$report = $this->getReport();
		if($report){
			$text .= "Report User: " . $report->getQMUser();
		}
		$address = $this->getRecipientEmailAddress();
		$text .= "Recipient: $address \n";
		$text .= "Response Code: $responseCode\n";
		$text .= "Server: " . (new ThisComputer)->getHost() . "\n";
		try {
			$text .= "Body: " . QMStr::truncate($this->getPlainText(), 500) . "\n";
		} catch (Throwable $e) {
			QMLog::info("Could not convert to plain text because: ".$e->getMessage());
		}
		foreach($this->attachmentDownloadUrls as $filename => $url){
			$slackAttachment = new SlackAttachment([]);
			$slackAttachment->setTitle($filename);
			$slackAttachment->setTitleLink($url);
			$m->attach($slackAttachment);
		}
		if(stripos($address, '@thinkbynumbers') === false && stripos($address, "@quantimo.do") === false){
			$m->send($text);
		} else{
			try {
				$m->setText($text);
			} catch (InvalidStringException $e) {
				QMLog::error(__METHOD__.": ".$e->getMessage());
			}
		}
		return $m;
	}
	/**
	 * @return string
	 */
	public function getPlainText(): string{
		$html = $this->getHtmlContent();
		try {
			return HtmlHelper::htmlToText($html);
		} catch (Throwable $e) {
			$url = S3Private::uploadHTML("debug/".QMStr::methodToPath(__METHOD__), $html, false);
			ExceptionHandler::dumpOrNotify($e);
			QMLog::logicExceptionIfNotProductionApiRequest("Could not convert this html ($url) to text because " .
				$e->getMessage());
			return $html;
		}
	}
	/**
	 * @param AnalyticalReport $report
	 */
	public function attachPdfFromReport(AnalyticalReport $report){
		$this->sourceObject = $report;
		$pdfPath = $report->getDownloadOrCreateFile(AnalyticalReport::FILE_TYPE_PDF);
		$url = $report->getUrlForFile(AnalyticalReport::FILE_TYPE_PDF);
		$this->addAttachmentByFilePath($pdfPath, $report->getTitleAttribute() . " PDF", $url);
	}
	private function recipientIsMikeOrQuantiModo(): bool{
		$address = $this->getRecipientEmailAddress();
		return stripos($address, "@thinkbynumbers.org") !== false || stripos($address, "@quantimo.do") !== false;
	}
	/**
	 * @return string|void
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function test(): string{
		$email = static::getTestInstance();
		/** @noinspection PhpUnhandledExceptionInspection */
		$email->setRecipientEmailAddress(Env::getRequired('TEST_EMAIL'));
		/** @noinspection PhpUnhandledExceptionInspection */
		$email->send();
		return $email->getHtmlContent();
	}
	public static function getTestInstance(): QMSendgrid{
		return new static(UserIdProperty::USER_ID_MIKE, "This is a test subject", "<div>This is test content</div>");
	}
	/**
	 * @return object
	 */
	private function getSourceObject(): object{
		return $this->sourceObject;
	}
}
