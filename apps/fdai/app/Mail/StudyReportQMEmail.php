<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Mail;
use App\Models\Study;
use App\Models\User;
use App\UI\HtmlHelper;
use SendGrid\Response;
class StudyReportQMEmail extends QMSendgrid {
	private $study;
	/**
	 * @param Study $study
	 * @param int|null $userId
	 */
	public function __construct(Study $study, int $userId = null){
		$this->sourceObject = $study;
		$this->study = $study;
		if(!$userId){
			$userId = $study->getUserId();
		}
		if(!$this->userId){
			$this->userId = $userId;
		}
		parent::__construct($userId);
	}
	public function send(): Response{
		// Don't do this stuff in constructor to avoid wasting time if we just need html body
		$study = $this->getStudy();
		$this->setReport($r = $study->getReport());
		$r->generateAndUploadHtmlAndPost();
		$this->attachPdfFromReport($r);
		return parent::send();
	}
	/**
	 * @return Study
	 */
	public function getStudy(): Study{
		return $this->study;
	}
	/**
	 * @return string
	 */
	public function getOrSetHtmlOrTemplateParams(): string{
		return HtmlHelper::renderView(view('study-email', [
			'study' => $this->getStudy(),
		]));
	}
	/**
	 * @return string
	 */
	public function getOrGenerateEmailSubject(): string{
		$subject = $this->getStudy()->getTitleAttribute();
		$this->setSubject($subject);
		return $subject;
	}
	/**
	 * @return QMSendgrid
	 */
	public static function getTestInstance(): QMSendgrid{
		return new static(User::mike()->getBestUserStudy()->getStudy(), User::mike()->getId());
	}
}
