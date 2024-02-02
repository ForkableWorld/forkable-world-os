<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Mail;
use App\Exceptions\InvalidEmailException;
use App\Exceptions\NoEmailAddressException;
use App\Models\User;
use App\Reports\DescriptiveOverviewReportForPhysician;
use App\Slim\Model\User\PhysicianUser;
use App\Slim\Model\User\QMUser;
class PatientDescriptiveOverviewEmail extends QMSendgrid {
	/** @var User */
	public $patient;
	/** @var PhysicianUser */
	public $physician;
	/**
	 * @param User $patient
	 * @param PhysicianUser $physician
	 * @throws NoEmailAddressException
	 * @throws InvalidEmailException
	 */
	public function __construct(User $patient, PhysicianUser $physician){
		$this->patient = $patient;
		$this->physician = $physician;
		$this->setRecipientAndEmailFromUserId($physician->getId());
		$this->attachPdfFromReport(new DescriptiveOverviewReportForPhysician($patient, $physician));
		parent::__construct();
	}
	/**
	 * @return array
	 */
	public function getOrSetHtmlOrTemplateParams(): array{
		$body = [
			'emailType' => $this->getType(),
			'headerText' => "Download the attachment to see an analysis of the factors that may be affecting your patient.",
		];
		return $body;
	}
	/**
	 * @return string
	 */
	public function getOrGenerateEmailSubject(): string{
		return $this->setSubject("Patient Update on " . $this->getPatient()->getDisplayNameAttribute());
	}
	/**
	 * @return PhysicianUser
	 */
	public function getPhysician(): PhysicianUser{
		return $this->physician;
	}
	/**
	 * @return QMUser
	 */
	public function getPatient(): QMUser{
		return $this->patient;
	}
	/**
	 * @return QMSendgrid
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function getTestInstance(): QMSendgrid{
		/** @noinspection PhpUnhandledExceptionInspection */
		return new static(User::mike(), User::mike()->getPhysicianUser());
	}
}
