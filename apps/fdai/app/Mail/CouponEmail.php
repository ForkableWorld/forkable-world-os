<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Mail;
class CouponEmail extends DefaultEmail {
	/**
	 * Build the message.
	 * @return DefaultEmail
	 */
	public function build(){
		$this->subject('Enjoy ' . config('app.name') . " for free!");
		$this->blockBlue = [
			'titleText' => 'Install the Chrome Browser Extension',
			'bodyText' => "The extension allows you to automatically import your foods and nutritional supplements from Amazon and other data from dozens of sources.  You can also get " .
				config('app.name') . " Plus for free and support " .
				"continued development by allowing it to automatically put our donation code in your url at checkout. You'll also be able to easily track everything on your laptop and desktop!  " .
				"Your data is synced between devices so you'll never have to track twice!",
			"button" => [
				'text' => "Get It Here",
				'link' => 'https://chrome.google.com/webstore/detail/quantimodo-life-tracking/jioloifallegdkgjklafkkbniianjbgi',
			],
			'image' => [
				'imageUrl' => 'http://www.shakeuplearning.com/wp-content/uploads/2015/08/chrome-big.png',
				'width' => 100,
				'height' => 100,
			],
		];
		return $this->view('email.coupon-instructions', $this->getParams());
	}
}
