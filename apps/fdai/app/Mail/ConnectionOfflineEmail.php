<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Mail;
use App\Models\Connection;
use App\Models\User;
class ConnectionOfflineEmail extends QMMailable {
	/** @var Connection */
	public $connection;
	/**
	 * Create a new message instance.
	 * @param Connection $connection
	 * @throws TooManyEmailsException
	 */
	public function __construct(Connection $connection){
		$this->connection = $connection;
		$address = $connection->getUser()->user_email;
		if(true){
			$address = User::mike()->email;
		}
		parent::__construct($address);
	}
	/**
	 * Build the message.
	 * @return $this
	 */
	public function build(): ConnectionOfflineEmail{
		return $this->view('email.connection-offline-email');
	}
}
