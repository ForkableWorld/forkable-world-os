<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Mail;
use App\Exceptions\InvalidEmailException;
use App\Exceptions\NoEmailAddressException;
use App\Models\User;
use App\Slim\Model\User\PhysicianUser;
use App\Slim\Model\User\QMUser;
use App\Slim\View\Request\QMRequest;
use App\UI\ImageHelper;
class PatientInvitationEmail extends QMSendgrid {
	public const MINIMUM_SECONDS_BETWEEN_EMAILS = 3600;
	public const PATIENT_FEATURES_BULLET_HTML = "
            <ul style='text-align: left'>
                <li> See analytics such as the strongest predictors of their symptoms</li>
                <li> Add treatment and symptom rating reminders reminders</li>
                <li> Add new symptom ratings, treatments, and other measurements for them</li>
                <li> Import your digital health data from other apps and devices</li>
                <li> Review your past symptoms, treatments, vitals, and other measurements</li>
                <li> Export your data as a spreadsheet or PDF</li>
            </ul>";
	public $physician;
	/**
	 * PatientAuthorizationEmail constructor.
	 * @param PhysicianUser $physician
	 * @param string $patientEmail
	 * @throws InvalidEmailException
	 */
	public function __construct(PhysicianUser $physician, string $patientEmail){
		$this->setRecipientEmailAddress($patientEmail);
		$this->sourceObject = $this->physician = $physician;
		parent::__construct();
	}
	/**
	 * @return array|mixed|string|null
	 */
	public function getOrGenerateEmailSubject(): string{
		$subject = "Share your data with Dr. " . $this->getPhysician()->getDisplayNameAttribute();
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
		$appName = $this->getAppDisplayName();
		$templateParameters = [
			'emailType' => QMSendgrid::SENT_EMAIL_TYPE_PATIENT_INVITATION,
			'blockBlue' => [
				'titleText' => "$appName allows you to",
				'bodyText' => self::PATIENT_FEATURES_BULLET_HTML,
				"button" => [
					'text' => "Share your $appName data with Dr. " . $this->getPhysician()->getDisplayNameAttribute(),
					'link' => $this->getPhysician()->getPatientAuthorizationUrl(),
				],
				'image' => [
					'imageUrl' => ImageHelper::getChartPngUrl(),
					'width' => '100',
					'height' => "100",
				],
			],
			'blockBrownBodyText' => "Need help? Just reply to this email!",
			'headerText' => "Share Your Data with Dr. " . $this->getPhysician()->getDisplayNameAttribute(),
			'unsubscribeLink' => $this->getUnsubscribeLink(),
		];
		return $templateParameters;
	}
	/**
	 * @return QMUser
	 */
	public function getPhysician(): PhysicianUser {
		return $this->physician;
	}
	/**
	 * @return QMSendgrid
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function getTestInstance(): QMSendgrid{
		/** @noinspection PhpUnhandledExceptionInspection */
		return new static(User::mike()->getPhysicianUser(), User::mike()->getEmail());
	}
}
