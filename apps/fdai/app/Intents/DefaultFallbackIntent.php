<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Intents;
use App\Properties\User\UserIdProperty;
use App\Slim\Model\Phrases\QuestionPhrase;
use App\Logging\QMLog;
use App\Slim\Model\User\QMUser;
class DefaultFallbackIntent extends QMIntent {
    public $actionName = 'input.unknown';
    public $phrases = [
        "After all we've been through! USER_INPUT! Is all you can say to me?"."What in the hell are you talking about? ",
        "USER_INPUT!  You kiss your mother with that mouth?",
        "Look, it's a simple question!  ORIGINAL_QUESTION",
        "Does not compute!"
    ];
    public $ignorePhrases = [
        "talk to"
    ];
    public function __construct(){
        parent::__construct();
    }
    /**
     * @return mixed|void
     */
    public function fulfillIntent(){
        $query = $this->getQuery();
        if(!$this->inIgnorePhrases($query)){
            $this->logError("Unknown input: ".$query);
            try {
                $p = new QuestionPhrase($query, [UserIdProperty::USER_ID_MIKE]);
                $p->saveAndSend();
            } catch (\Throwable $e){
                QMLog::error(__METHOD__.": ".$e->getMessage());
            }
            $this->addMessage("I'm going to ask my creator about $query and get back to you.   ");
            //$this->respondWithInstructions();  // This is handled in general response
        }
    }
    /**
     * @param $query
     * @return bool
     */
    private function inIgnorePhrases($query){
        foreach($this->ignorePhrases as $phrase){
            if(stripos($query, $phrase) !== false){
                return true;
            }
        }
        return false;
    }
}
