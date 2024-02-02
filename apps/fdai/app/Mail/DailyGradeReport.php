<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Mail;
class DailyGradeReport extends QMMailable {
	/**
	 * Build the message.
	 * @return $this
	 */
	public function build(){
		return $this->view('view.name');
	}
}
