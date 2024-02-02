<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Intents;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\QMUnit;
use App\Variables\QMUserVariable;
class RecordSymptomIntent extends QMIntent {
    public function __construct(){
        parent::__construct();
    }
    /**
     * @return mixed|void
     */
    public function fulfillIntent(){
        $value = $this->getMeasurementValue();
        $userVariable = $this->getUserVariable();
        $userVariable->addToMeasurementQueue(new QMMeasurement(time(), $value));
        $userVariable->saveMeasurements();
        $message = "I've recorded ".QMUnit::getOneToFiveRating()
                ->getValueAndUnitString($value)." ".$userVariable->getOrSetVariableDisplayName().". ";
        if($userVariable->getOptimalValueMessage()){
            $message .= $userVariable->getOptimalValueMessage();
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
    protected function saveMeasurement(){
        if($this->getMeasurementValue() !== null){
            $value = $this->getMeasurementValue();
            $userVariable = $this->getUserVariable();
            $userVariable->addToMeasurementQueue(new QMMeasurement(time(), $value));
            $userVariable->saveMeasurements();
            if($userVariable->getOptimalValueMessage()){
                $this->addMessage($userVariable->getOptimalValueMessage());
            }
        }
        return false;
    }
    /**
     * @return QMUserVariable
     */
    public function getUserVariable(): QMUserVariable{
        $variableName = $this->getVariableName();
        $user = QMAuth::getQMUser();
        $variable = QMUserVariable::findOrCreateByNameOrIdOrSynonym($user->id, $variableName,
            [], ['unitAbbreviatedName' => '/5']);
        return $variable;
    }
    /**
     * @return float
     */
    public function getVariableName(){
        $variableName = $this->getParam([
            'variableName',
            'symptomVariableName'
        ]);
        return $variableName;
    }
}
