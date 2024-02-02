<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Mail;
use App\Models\User;
use App\Properties\User\UserIdProperty;
use App\Reports\AnalyticalReport;
use App\Reports\RootCauseAnalysis;
use ReflectionException;
use SendGrid\Response;
class RootCauseAnalysisEmail extends QMSendgrid {
	/**
	 * @param RootCauseAnalysis $analysis
	 * @param int|null $recipientUserId
	 */
	public function __construct(RootCauseAnalysis $analysis, int $recipientUserId = null){
		$this->sourceObject = $this->report = $analysis;
		$this->userId = $recipientUserId ?: $analysis->getUserId();
		$this->userId = UserIdProperty::USER_ID_MIKE;
		$html = $analysis->getOrGenerateEmailHtml();
		$this->setHtmlContent($html);
		//$this->addSpreadsheetAttachment();
		$this->attachPdfFromReport($analysis);
		parent::__construct();
	}
	/**
	 * Keep this function so RootCauseAnalysis is type hinted for PHPStorm
	 * @return RootCauseAnalysis
	 */
	public function getReport(): AnalyticalReport{
		return $this->report;
	}
	/**
	 * @return array
	 */
	public function getOrSetHtmlOrTemplateParams(): array{
		return [
			'emailType' => $this->getType(),
			'headerText' => "Download the attachment to see an analysis of the factors that may be influencing your " .
				$this->getReport()->getOutcomeQMUserVariable()->getOrSetVariableDisplayName(),
		];
	}
	/**
	 * @return string
	 */
	public function getOrGenerateEmailSubject(): string{
		$subject =
			"Factors Influencing Your " . $this->getReport()->getOutcomeQMUserVariable()->getOrSetVariableDisplayName();
		$this->setSubject($subject);
		return $subject;
	}
	private function addSpreadsheetAttachment(): void{
		$a = $this->getReport();
		$filePath = $a->generateAndUploadXls();
		if(!$filePath){
			$this->logError("No spreadsheet to attach!");
			return;
		}
		$url = $a->getUrlForFile(AnalyticalReport::FILE_TYPE_XLS);
		$this->addAttachmentByFilePath($filePath, $a->getTitleAttribute() . " Spreadsheet", $url);
	}
	/**
	 * @return Response|void
	 */
	public function getHtmlContent(): string{
		try {
			return (new RootCauseAnalysisMail($this->getReport()))->render();
		} catch (ReflectionException $e) {
		}
	}
	/**
	 * @return QMSendgrid
	 */
	public static function getTestInstance(): QMSendgrid{
		return new static(User::mike()->getRootCauseAnalysis(), User::mike()->getId());
	}
}
