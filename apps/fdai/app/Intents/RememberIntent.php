<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Intents;
use App\Slim\Model\Phrases\Phrase;
class RememberIntent extends QMIntent {
    public $actionName = 'remember';
    public $triggerPhrases = ["remember"];
    public function __construct(){
        parent::__construct();
    }
    /**
     * @return mixed|void
     */
    public function fulfillIntent(){
        $user = $this->getUser();
        $answer = new Phrase($this->getParam('memoryAnswer'));
        $answerId = $answer->save();
        $question = $this->getParam('memoryQuestion');
        $questionPhrase = Phrase::getOrCreate([
            Phrase::FIELD_USER_ID => $user->getId(),
            Phrase::FIELD_TEXT    => $question
        ]);
        $questionPhrase->setResponsePhraseId($answerId);
        $questionPhrase->save();
        $this->addMessage("OK. When you want me to remind you, just say RECALL $question");
    }
    /**
     * @return float
     */
    public function getMeasurementValue(){
        $value = $this->getParam([
            'measurementValue',
            'value'
        ]);
        return $value;
    }
}
