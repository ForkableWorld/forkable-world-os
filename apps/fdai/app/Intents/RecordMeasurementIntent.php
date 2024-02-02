<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Intents;
use App\Models\Measurement;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\QMUnit;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;

class RecordMeasurementIntent extends QMIntent {
    public $actionName = 'record_measurement';
    public $triggerPhrases = [
        "record",
        "record",
        "add a measurement",
        "record measurement"
    ];
    /**
     * @return void
     * @throws \App\Exceptions\ModelValidationException
     */
    public function fulfillIntent(){
        $client = $this->getWebhookClient();
        $data = $client->getParameters();
        $data[Measurement::FIELD_START_TIME] = time();
        $m = Measurement::fromData($data);
        $uv = $m->getUserVariable();
        $uv->updateFromMeasurements([$m]);
        $unit = $uv->getUserUnit();
        $message = "I've recorded ".$unit->getValueAndUnitString($m->original_value)." ".
            $uv->getDisplayNameAttribute().". ";
        if($ovm = $uv->optimal_value_message){
            $message .= $ovm;
        }
        $this->addMessage($message);
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
    /**
     * @return QMUserVariable
     */
    public function getUserVariable(): QMUserVariable{
        $variableName = $this->getVariableName();
        $user = QMAuth::getQMUser();
        $variable = QMUserVariable::findOrCreateByNameOrIdOrSynonym($user->getId(), $variableName);
        return $variable;
    }
    /**
     * @return float
     */
    public function getVariableName(){
        if($this->commonVariableFromQuery){
            return $this->getCommonVariableFromQuery()->name;
        }
        if($this->getWebhookParamFromAnyContext('mood')){
            return "Overall Mood";
        }
        $variableName = $this->getParam('variableName');
        if(empty($variableName)){
            $variableName = $this->getQuery();
        }
        if(empty($variableName) && $this->getCommonVariableFromQuery()){
            $commonVariable = $this->getCommonVariableFromQuery();
            $variableName = $commonVariable->name;
        }
        return $variableName;
    }
    /**
     * @return bool|QMCommonVariable
     */
    protected function getCommonVariableFromQuery(){
        if($this->commonVariableFromQuery !== null){
            return $this->commonVariableFromQuery;
        }
        $query = $this->getQuery(true);
        if(!$query){
            return false;
        }
        if(strlen($query) < 3){
            return false;
        }
        $variable = QMCommonVariable::findByNameIdOrSynonym($query);
        if(!$variable){
            $variable = false;
        }
        return $this->commonVariableFromQuery = $variable;
    }
    /**
     * @return QMUnit
     */
    private function getUnit(){
        $unitName = $this->getParam([
            'unitName',
            'unitAbbreviatedName'
        ]);
        if(!empty($unitName)){
            $unit = QMUnit::findByNameOrSynonym($unitName);
        }
        if(!isset($unit)){
            $unit = $this->getUserVariable()->getUserUnit();
        }
        return $unit;
    }
}
