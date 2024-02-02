<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Intents;
use App\Slim\Middleware\QMAuth;
use App\Types\QMStr;
use App\Variables\QMUserVariable;
use App\Variables\QMVariableCategory;
class CreateReminderIntent extends QMIntent {
    public $triggerPhrases = [
        "add reminder",
        "add",
        "add reminder",
        "add a reminder for",
        "create reminder for",
        "create a reminder for"
    ];
    public $variableName;
    public function __construct(){
        parent::__construct();
    }
    /**
     * @return mixed|void
     */
    public function fulfillIntent(){
        $this->createReminder();
    }
    /**
     * @return string
     */
    public function getVariableName(){
        return $this->getParam('variableName');
    }
    /**
     * @return bool
     */
    protected function askWhatVariableReminderToAdd(){
        if(stripos($this->getQuery(), 'add a ') === false){
            return false;
        }
        $variableCategoryName = QMStr::after('add a ', $this->getQuery());
        $variableCategory = QMVariableCategory::find($variableCategoryName);
        if(!$variableCategory){
            $this->addMessage("I couldn't find a category called $variableCategoryName.  Please try add a treatment, food, symptom, or emotion.");
        }
        $this->addMessage("What ".$variableCategory->getNameSingular()." would you like to add?");
        $this->setOutgoingContext(self::CONTEXT_CREATE_REMINDER, ['variableCategoryName' => $variableCategoryName]);
        return true;
    }
    /**
     * @return void
     * @throws \App\Exceptions\ModelValidationException
     */
    protected function createReminder(){
        $params = [];
        if($cat = $this->getVariableCategoryName()){$params['variableCategoryName'] = $cat;}
        $variableName = $this->getVariableName();
        $variable = QMUserVariable::findOrCreateByNameOrIdOrSynonym(QMAuth::getQMUser()->id, $variableName, $params, $params);
        $reminder = $variable->createTrackingReminder();
        // Doesn't seem to work
        $this->getWebhookClient()->clearOutgoingContext(self::CONTEXT_CREATE_REMINDER);
        $this->addMessage("OK. I'll ask you about your ".$variable->getOrSetVariableDisplayName()." once a day. ");
        $n = $reminder->getTrackingReminderNotification();
        $this->setNewNotification($n->getDBModel());
        //$this->respondWithNotification();  // Response is handled in handleResponse function
    }
    /**
     * @return string
     */
    protected function getVariableCategoryName(){
        $variableCategoryName = $this->getWebhookParamFromAnyContext('variableCategoryName');
        return $variableCategoryName;
    }
}
