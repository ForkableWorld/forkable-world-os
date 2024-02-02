<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Mail;
class DefaultEmail extends QMMailable {
	/**
	 * @var array
	 */
	protected $blockBlue;
	/**
	 * @var array
	 */
	protected $blockOrange;
	/**
	 * @var array
	 */
	protected $blockBrownBodyText = "Any interesting discoveries? <br> Let us know on social media!";
	protected $headerText;
	/**
	 * Create a new message instance.
	 * @param string $address
	 * @param array $params
	 * @throws TooManyEmailsException
	 */
	public function __construct(string $address, array $params = []){
		$this->params = $params;
		parent::__construct($address, $params);
		$this->headerText = $params['headerText'] ?? $params['subject'] ?? null;
	}
	/**
	 * Build the message.
	 * @return $this
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function build(){
		return $this->view('email.default-email', $this->getParams());
	}
}
