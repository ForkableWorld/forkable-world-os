<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Mail;
use App\Reports\AnalyticalReport;
use App\Storage\S3\S3Private;
class AnalyticalReportMail extends QMMailable {
	/** @var AnalyticalReport */
	public $report;
	/**
	 * Create a new message instance.
	 * @param AnalyticalReport $report
	 * @param string $address
	 * @throws TooManyEmailsException
	 */
	public function __construct(AnalyticalReport $report, string $address){
		$this->report = $report;
		parent::__construct($address);
	}
	/**
	 * Build the message.
	 * @return $this
	 */
	public function build(){
		$r = $this->report;
		$path = $r->getS3FilePath(AnalyticalReport::FILE_TYPE_PDF);
		$html = $r->getOrGenerateEmailHtml();
		return $this->view('email.reports.report')->text('email.reports.report_plain')
			->attachFromStorageDisk(S3Private::DISK_NAME, $path, $r->getTitleAttribute() . '.pdf', [
				'mime' => 'application/pdf',
			]);
	}
}
