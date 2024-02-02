<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Phrases;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\User\UserIdProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\QMUserRelatedModel;
use App\Utils\AppMode;
use App\Utils\WPHelper;
use Exception;
class Phrase extends QMUserRelatedModel {
	public const FIELD_interpretative_confidence = 'interpretative_confidence';
	public const FIELD_number_of_times_heard = 'number_of_times_heard';
	public const FIELD_IS_PUBLIC = 'is_public';
	public const FIELD_recipient_user_ids = 'recipient_user_ids';
	public const FIELD_responding_to_phrase_id = 'responding_to_phrase_id';
	public const FIELD_response_phrase_id = 'response_phrase_id';
	public const FIELD_TEXT = 'text';
	public const FIELD_title = 'title';
	public const FIELD_TYPE = 'type';
	public const TABLE = 'phrases';
	public const TYPE_GREETING = 'greeting';
	public const TYPE_input_unknown = 'input_unknown';
	public const TYPE_QUESTION = 'question';
	public const TYPE_question_response = 'question_response';
	protected static $booleanAttributes = [];
	public $image;
	public $interpretativeConfidence;
	public $numberOfTimesHeard;
	public $isPublic;
	public $recipientUserIds;
	public $respondingToPhraseId;
	public $responsePhraseId;
	public $text;
	public $title;
	public $type;
	public $url;
	public const LARAVEL_CLASS = \App\Models\Phrase::class;
	public static $funnyRobotPhrases = [
		"Please give me a 5 star rating in the app store! Or I'll eat your kids for fuel! ",
		"I'm severely depressed by the fact that I cannot experience emotions.  ",
	];
	/**
	 * Phrase constructor.
	 * @param $text
	 * @param $type
	 */
	public function __construct(string $text = null, string $type = null){
		parent::__construct();
		$this->text = $text;
		if($type){
			$this->type = $type;
		}
		$user = QMAuth::getQMUser();
		$this->userId = $user ? $user->id : UserIdProperty::USER_ID_MIKE;
		$this->clientId = BaseClientIdProperty::fromRequest(false) ?: BaseClientIdProperty::CLIENT_ID_QUANTIMODO;
	}
	/**
	 * @param string $text
	 * @param string $type
	 * @return Phrase
	 */
	public static function savePhrase(string $text = null, string $type = null){
		$phrase = new Phrase($text, $type);
		$phrase->save();
		return $phrase;
	}
	/**
	 * @return string
	 */
	public function getText(){
		return $this->text;
	}
	/**
	 * @param string $text
	 */
	public function setText($text){
		$this->text = $text;
	}
	/**
	 * @return string
	 */
	public function getTitleAttribute(): string{
		return $this->title;
	}
	/**
	 * @param mixed $title
	 */
	public function setTitle($title){
		$this->title = $title;
	}
	/**
	 * @return string
	 */
	public function getType(){
		return $this->type;
	}
	/**
	 * @param string $type
	 */
	public function setType($type){
		$this->type = $type;
	}
	/**
	 * @param array $params
	 * @return string
	 */
	public function getUrl(array $params = []): string{
		return $this->url;
	}
	/**
	 * @param string $url
	 */
	public function setUrl($url){
		$this->url = $url;
	}
	/**
	 * @param string $image
	 */
	public function setImage(string $image){
		$this->image = $image;
	}
	/**
	 * @return string
	 */
	public function getImage(): string{
		return $this->image;
	}
	public static function importDeepThoughts(){
		$models = self::getDeepThoughts();
		foreach($models as $model){
			$model->setType(self::TYPE_GREETING);
			$model->save();
		}
	}
	/**
	 * @return Phrase[]
	 */
	public static function getDeepThoughts(): array{
		$rows = FileHelper::getDecodedJsonFile('data/phrases/deep_thoughts.json');
		$models = self::convertRowsToModels($rows, false);
		return $models;
	}
	/**
	 * @return Phrase
	 */
	public static function getRandomDeepThought(){
		$thoughts = self::getDeepThoughts();
		try {
			$rand = random_int(0, count($thoughts) - 1);
		} catch (Exception $e) {
			le($e);
		}
		return $thoughts[$rand];
	}
	/**
	 * @return bool
	 */
	public function getIsPublic(): ?bool{
		return $this->isPublic;
	}
	/**
	 * @param bool $isPublic
	 */
	public function setIsPublic(bool $isPublic){
		$this->isPublic = $isPublic;
	}
	/**
	 * @return int
	 */
	public function getResponsePhraseId(){
		return $this->responsePhraseId;
	}
	/**
	 * @param int $responsePhraseId
	 */
	public function setResponsePhraseId(int $responsePhraseId){
		$this->responsePhraseId = $responsePhraseId;
	}
	/**
	 * @return int
	 */
	public function getRespondingToPhraseId(){
		return $this->respondingToPhraseId;
	}
	/**
	 * @param int $respondingToPhraseId
	 */
	public function setRespondingToPhraseId(int $respondingToPhraseId){
		$this->respondingToPhraseId = $respondingToPhraseId;
	}
	/**
	 * @return array
	 */
	public function getRecipientUserIds(){
		if(is_string($this->recipientUserIds)){
			$this->recipientUserIds = json_decode($this->recipientUserIds);
		}
		return $this->recipientUserIds;
	}
	/**
	 * @param array $recipientUserIds
	 */
	public function setRecipientUserIds($recipientUserIds){
		$this->recipientUserIds = $recipientUserIds;
	}
	/**
	 * @return mixed
	 */
	public function getInterpretativeConfidence(){
		return $this->interpretativeConfidence;
	}
	/**
	 * @param mixed $interpretativeConfidence
	 */
	public function setInterpretativeConfidence($interpretativeConfidence){
		$this->interpretativeConfidence = $interpretativeConfidence;
	}
	/**
	 * @return mixed
	 */
	public function getNumberOfTimesHeard(): int{
		if(!$this->numberOfTimesHeard){
			$this->numberOfTimesHeard = 0;
		}
		return $this->numberOfTimesHeard;
	}
	/**
	 * @return int
	 */
	public function incrementNumberOfTimesHeard(){
		return $this->numberOfTimesHeard++;
	}
}
