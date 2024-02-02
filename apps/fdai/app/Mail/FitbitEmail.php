<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Mail;
class FitbitEmail extends QMMailable {
	/**
	 * Build the message.
	 * @return $this
	 */
	public function build(){
		$this->subject('Automate Your Sleep, Heart Rate, and Activity Tracking With Fitbit');
		return $this->view('email.fitbit', $this->params);
	}
}
