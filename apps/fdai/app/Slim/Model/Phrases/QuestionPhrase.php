<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Phrases;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NoDeviceTokensException;
use App\Slim\Model\User\QMUser;
class QuestionPhrase extends Phrase {
	public $type = self::TYPE_QUESTION;
	/**
	 * Phrase constructor.
	 * @param string $text
	 * @param array|null $recipientUserIds
	 */
	public function __construct(string $text, array $recipientUserIds){
		parent::__construct($text);
		$this->recipientUserIds = $recipientUserIds;
	}
	/**
	 * @throws NoDeviceTokensException
	 */
	public function send(): void{
		foreach($this->recipientUserIds as $id){
			$user = QMUser::find($id);
			$user->askQuestion($this);
		}
	}
	/**
	 * @throws NoDeviceTokensException
	 */
	public function saveAndSend(): void{
		try {
			$this->save();
		} catch (ModelValidationException $e) {
			le($e);
		}
		$this->send();
	}
}
