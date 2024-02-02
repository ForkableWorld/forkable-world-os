<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Intents;
use App\Slim\Model\Phrases\Phrase;
class DefaultWelcomeIntent extends QMIntent {
    public $actionName = 'input.welcome';
    public function __construct(){
        parent::__construct();
    }
    /**
     * @return mixed|void
     */
    public function fulfillIntent(){
        $text = Phrase::getRandomDeepThought()->text."...  ";
        $this->addMessage($text);
        //$this->respondWithInstructions(); Handled in general response
    }
}
