<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Mail;
class BecomeAScientistEmail extends DefaultEmail {
	public function build(){
		$this->params = [
			'titleText' => 'Become a Scientist',
			'bodyText' => 'Help use minimize suffering with data!',
			"button" => [
				'text' => 'Start Tracking',
				'link' => getHostAppSettings()->additionalSettings->downloadLinks->webApp,
			],
			'image' => [
				'imageUrl' => "https://www.filepicker.io/api/file/tjUsYjIHSDCkrrniLuev",
				'width' => '145',
				'height' => "89",
			],
		];
		return parent::build();
	}
}
