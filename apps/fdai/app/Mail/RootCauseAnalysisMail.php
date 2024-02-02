<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Mail;
use App\Reports\RootCauseAnalysis;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class RootCauseAnalysisMail extends Mailable {
	use Queueable, SerializesModels;
	/** @var RootCauseAnalysis */
	public $report;
	/**
	 * Create a new message instance.
	 * @param RootCauseAnalysis $report
	 */
	public function __construct(RootCauseAnalysis $report){
		$this->report = $report;
	}
	/**
	 * Build the message.
	 * @return $this
	 */
	public function build(){
		return $this->view('email.reports.root-cause-analysis-email');
	}
}
