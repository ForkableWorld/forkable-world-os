<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Mail;
class PostListMail extends QMMailable {
	const BLADE = 'email.posts';
	/**
	 * Build the message.
	 * @return $this
	 */
	public function build(){
		return $this->markdown(self::BLADE);
	}
}
