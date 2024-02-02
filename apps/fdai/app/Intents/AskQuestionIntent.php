<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Intents;
use App\Properties\User\UserIdProperty;
use App\Slim\Model\Phrases\QuestionPhrase;
use App\Slim\Model\User\QMUser;
class AskQuestionIntent extends QMIntent {
    public function __construct(){
        parent::__construct();
    }
    /**
     * @return mixed|void
     */
    public function fulfillIntent(){
        $query = $this->getQuery();
        $this->logError("Unknown input: ".$query);
        $p = new QuestionPhrase($query, [UserIdProperty::USER_ID_MIKE]);
        $p->saveAndSend();
        $this->addMessage("I'm going to ask my creator about $query and get back to you.   ");
    }
}
