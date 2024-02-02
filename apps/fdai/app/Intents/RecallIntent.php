<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Intents;
use App\Slim\Model\Phrases\Phrase;
class RecallIntent extends QMIntent {
    public $actionName = 'recall';
    public $triggerPhrases = ["recall"];
    public function __construct(){
        parent::__construct();
    }
    /**
     * @return mixed|void
     */
    public function fulfillIntent(){
        $user = $this->getUser();
        $question = $this->getParam('memoryQuestion');
        $questionPhrase = Phrase::findByArray([
            Phrase::FIELD_USER_ID => $user->getId(),
            Phrase::FIELD_TEXT    => $question
        ]);
        $answerId = $questionPhrase->getResponsePhraseId();
        $answerPhrase = Phrase::findByArray([Phrase::FIELD_ID => $answerId]);
        if(!$answerPhrase){
            $this->addMessage("I couldn't find the answer to $question.  Could you try a different phrasing you might have used originally?");
        }else{
            $this->addMessage("The answer to the question $question is ".$answerPhrase->text."!  ");
        }
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
