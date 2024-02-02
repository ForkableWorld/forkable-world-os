<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace Netatmo\Objects;
use Netatmo\Common\NACameraHomeInfo;
use Netatmo\Common\NASDKErrorCode;
use Netatmo\Exceptions\NASDKException;
/** Class NAHome
 */
class NAHome extends NAObject {
    /**
     * NAHome constructor.
     * @param $array
     */
    public function __construct($array){
        parent::__construct($array);
        if(isset($array[NACameraHomeInfo::CHI_PERSONS])){
            $personArray = [];
            foreach($array[NACameraHomeInfo::CHI_PERSONS] as $person){
                $personArray[] = new NAPerson($person);
            }
            $this->object[NACameraHomeInfo::CHI_PERSONS] = $personArray;
        }
        if(isset($array[NACameraHomeInfo::CHI_EVENTS])){
            $eventArray = [];
            foreach($array[NACameraHomeInfo::CHI_EVENTS] as $event){
                $eventArray[] = new NAEvent($event);
            }
            $this->object[NACameraHomeInfo::CHI_EVENTS] = $eventArray;
        }
        if(isset($array[NACameraHomeInfo::CHI_CAMERAS])){
            $cameraArray = [];
            foreach($array[NACameraHomeInfo::CHI_CAMERAS] as $camera){
                $cameraArray[] = new NACamera($camera);
            }
            $this->object[NACameraHomeInfo::CHI_CAMERAS] = $cameraArray;
        }
    }
    /**
     * @return string
     * @brief returns home's name
     */
    public function getName(){
        return $this->getVar(NACameraHomeInfo::CHI_NAME);
    }
    /**
     * @return object of event objects
     * @brief returns home timeline of event
     */
    public function getEvents(){
        return $this->getVar(NACameraHomeInfo::CHI_EVENTS, []);
    }
    /**
     * @return object of person objects
     * @brief returns every person belonging to this home
     */
    public function getPersons(){
        return $this->getVar(NACameraHomeInfo::CHI_PERSONS, []);
    }
    /**
     * @return array of person objects
     * @brief returns every known person belonging to this home
     */
    public function getKnownPersons(){
        $knowns = [];
        foreach($this->getVar(NACameraHomeInfo::CHI_PERSONS, []) as $person){
            if($person->isKnown()){
                $knowns[] = $person;
            }
        }
        return $knowns;
    }
    /**
     * @return array of person objects
     * @brief returns every unknown person belonging to this home
     */
    public function getUnknownPersons(){
        $unknowns = [];
        foreach($this->getVar(NACameraHomeInfo::CHI_PERSONS, []) as $person){
            if($person->isUnknown()){
                $unknowns[] = $person;
            }
        }
        return $unknowns;
    }
    /**
     * @return object of camera objects
     * @brief returns every camera belonging to this home
     */
    public function getCameras(){
        return $this->getVar(NACameraHomeInfo::CHI_CAMERAS, []);
    }
    /**
     * @return string
     * @brief returns home's timezone
     */
    public function getTimezone(){
        $place = $this->getVar(NACameraHomeInfo::CHI_PLACE);
        return $place['timezone'] ?? 'GMT';
    }
    /**
     * @param $camera_id
     * @return NACamera
     * @throws NASDKException
     * @brief return the camera object corresponding to the id asked
     * @throw NASDKErrorException
     */
    public function getCamera($camera_id){
        foreach($this->getVar(NACameraHomeInfo::CHI_CAMERAS, []) as $camera){
            if($camera->getId() === $camera_id){
                return $camera;
            }
        }
        throw new NASDKException(NASDKErrorCode::NOT_FOUND, "camera $camera_id not found in home: ".$this->getId());
    }
    /**
     * @param $person_id
     * @return NAPerson
     * @throws NASDKException
     * @brief returns NAPerson object corresponding to the id in parameter
     * @throw NASDKErrorException
     */
    public function getPerson($person_id){
        foreach($this->getVar(NACameraHomeInfo::CHI_PERSONS, []) as $person){
            if($person->getId() === $person_id){
                return $person;
            }
        }
        throw new NASDKException(NASDKErrorCode::NOT_FOUND, "person $person_id not found in home: ".$this->getId());
    }
    /**
     * @return array of NAPerson
     * @brief returns every person that are not home
     */
    public function getPersonAway(){
        $away = [];
        foreach($this->getVar(NACameraHomeInfo::CHI_PERSONS, []) as $person){
            if($person->isAway()){
                $away[] = $person;
            }
        }
        return $away;
    }
    /**
     * @return array of NAPerson
     * @brief returns every person that are home
     */
    public function getPersonAtHome(){
        $home = [];
        foreach($this->getVar(NACameraHomeInfo::CHI_PERSONS, []) as $person){
            if(!$person->isAway()){
                $home[] = $person;
            }
        }
        return $home;
    }
}
?>
