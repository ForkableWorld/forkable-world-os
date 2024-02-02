<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Mail;
use App\Exceptions\NoEmailAddressException;
use App\Models\User;
use App\Slim\Model\User\PhysicianUser;
use App\Slim\Model\User\QMUser;
use App\Slim\View\Request\QMRequest;
use App\UI\ImageHelper;
class PhysicianInvitationEmail extends QMSendgrid {
	public const MINIMUM_SECONDS_BETWEEN_EMAILS = 3600;
	public const PHYSICIAN_FEATURES_BULLET_HTML = "
            <ul style='text-align: left'>
                <li> See analytics such as the strongest predictors of their symptoms</li>
                <li> Add treatment and symptom rating reminders reminders</li>
                <li> Add new symptom ratings, treatments, and other measurements for them</li>
                <li> Import their digital health data from other apps and devices</li>
                <li> Review their past symptoms, treatments, vitals, and other measurements</li>
                <li> Export their data as a spreadsheet or PDF</li>
            </ul>";
	public const PHYSICIAN_URL = "https://physician.quantimo.do";
	private $patient;
	/**
	 * PatientAuthorizationEmail constructor.
	 * @param PhysicianUser $physician
	 * @param User $patient
	 */
	public function __construct(PhysicianUser $physician, User $patient){
		$this->sourceObject = $this->patient = $patient;
		parent::__construct($physician->id);
	}
	/**
	 * @return null|string
	 */
	private function getHeaderText(): ?string{
		$providedText = QMRequest::getParam('emailBody', null, false);
		return $providedText;
	}
	/**
	 * @return array|mixed|string|null
	 */
	public function getOrGenerateEmailSubject(): string{
		$subject = $this->getPatient()->displayName . " wants to share their data with you!";
		$providedSubject = QMRequest::getParam('emailSubject');
		if($providedSubject){
			$subject = $providedSubject;
		}
		$this->setSubject($subject);
		return $subject;
	}
	/**
	 * @return array
	 */
	public function getOrSetHtmlOrTemplateParams(): array{
		$templateParameters = [
			'blockBlue' => [
				'titleText' => "This will allow you to",
				'bodyText' => self::PHYSICIAN_FEATURES_BULLET_HTML,
				"button" => [
					'text' => "View " . ucfirst($this->getPatient()->displayName) . "'s Data",
					'link' => self::PHYSICIAN_URL,
				],
				'image' => [
					'imageUrl' => ImageHelper::getChartPngUrl(),
					'width' => '100',
					'height' => "100",
				],
			],
			'blockBrownBodyText' => "Need help? Just reply to this email!",
			'headerText' => $this->getHeaderText(),
			'unsubscribeLink' => $this->getUnsubscribeLink(),
		];
		return $templateParameters;
	}
	/**
	 * @return QMUser
	 */
	private function getPatient(): User {
		return $this->patient;
	}
	/**
	 * @return string
	 */
	public static function getPhysicianFeaturesBlock(): string{
		$url = self::PHYSICIAN_URL;
		return "<div>
            <h3>You can also go to <a href='$url'>$url</a> to:</h3>" . self::PHYSICIAN_FEATURES_BULLET_HTML . "</div>";
	}
	/**
	 * @return QMSendgrid
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function getTestInstance(): QMSendgrid{
		/** @noinspection PhpUnhandledExceptionInspection */
		return new static(User::mike()->getPhysicianUser(), User::mike());
	}
}
