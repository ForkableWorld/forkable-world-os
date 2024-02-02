<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Mail;
use App\Exceptions\InvalidEmailException;
use App\Exceptions\NoEmailAddressException;
use App\Models\User;
use App\Reports\AnalyticalReport;
use App\Reports\DescriptiveOverviewReportForPhysician;
use App\Slim\Model\User\PhysicianUser;
use App\Slim\Model\User\QMUser;
class DescriptiveOverviewForAllPatientsEmail extends QMSendgrid {
	/** @var QMUser */
	public $patient;
	/** @var PhysicianUser */
	public $physician;
	/**
	 * @param PhysicianUser $physician
	 * @throws NoEmailAddressException
	 * @throws InvalidEmailException
	 */
	public function __construct(PhysicianUser $physician){
		$this->physician = $physician;
		$this->setRecipientAndEmailFromUserId($physician->getId());
		$patients = $physician->getPatients();
		foreach($patients as $patient){
			$analysis = new DescriptiveOverviewReportForPhysician($patient, $physician);
			$pdfPath = $analysis->getDownloadOrCreateFile(AnalyticalReport::FILE_TYPE_PDF);
			$url = $analysis->getUrlForFile(AnalyticalReport::FILE_TYPE_PDF);
			$this->addAttachmentByFilePath($pdfPath, $analysis->getTitleWithUserName() . " PDF", $url);
		}
		parent::__construct();
	}
	/**
	 * @return string
	 */
	public function getOrSetHtmlOrTemplateParams(): string{
		return "<h1>See the attached PDFs for an overviews of your patients data.</h1>" .
			PhysicianInvitationEmail::getPhysicianFeaturesBlock();
	}
	/**
	 * @return string
	 */
	public function getOrGenerateEmailSubject(): string{
		return $this->setSubject("Patient Data Update");
	}
	/**
	 * @return PhysicianUser
	 */
	public function getPhysician(): PhysicianUser{
		return $this->physician;
	}
	/**
	 * @return QMSendgrid
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function getTestInstance(): QMSendgrid{
		/** @noinspection PhpUnhandledExceptionInspection */
		return new static(User::mike()->getPhysicianUser());
	}
}
