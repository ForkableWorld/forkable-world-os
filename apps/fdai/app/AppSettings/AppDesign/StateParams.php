<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings\AppDesign;
use App\Slim\Model\StaticModel;
use App\Variables\QMVariableCategory;
class StateParams extends StaticModel{
    public $ionIcon;
    /**
     * StateParams constructor.
     * @param null $object
     */
    public function __construct($object = null){
		if($object){
			$this->populateFieldsByArrayOrObject($object);
			$this->setFallbackProperties($object);
		}
    }
    /**
     * @param string $name
     * @return bool
     */
    public function hasProperty(string $name){
        return property_exists($this, $name);
    }
    /**
     * @return bool
     */
    public function hasVariableCategoryProperty(){
        return $this->hasProperty('variableCategoryName');
    }
    /**
     * @return null
     */
    public function getVariableCategoryName(){
        if($this->hasVariableCategoryProperty()){
            return $this->variableCategoryName;
        }
    }
    /**
     * @param string $variableCategoryName
     * @return string
     */
    public function setVariableCategoryName(string $variableCategoryName){
        if($variableCategoryName == ':variableCategoryName'){
            return false;
        }
        if($variableCategoryName === "Anything"){
            return $this->variableCategoryName = $variableCategoryName;
        }
        $variableCategoryName = str_replace("+", " ", $variableCategoryName);
        $category = QMVariableCategory::findByNameOrSynonym($variableCategoryName, false);
        if(!$category){
            $this->logError("Could not find category for $variableCategoryName");
            return false;
        }
        return $this->variableCategoryName = $category->getNameAttribute();
    }
    /**
     * @param string $ionIcon
     */
    public function setIonIcon(string $ionIcon){
        $this->ionIcon = $ionIcon;
    }
    /**
     * @return string
     */
    public function getIonIcon(): string{
        return $this->ionIcon;
    }
    /**
     * @return string
     */
    public function getVariableName(){
        if(property_exists($this, 'variableName')){
            return $this->variableName;
        }
    }
    /**
     * @param mixed $variableName
     */
    public function setVariableName(string $variableName){
        $this->variableName = $variableName;
    }
    /**
     * @param array|object $arrayOrObject
     * @return StateParams|bool
     */
    public static function instantiateIfNecessary(array|object|string $arrayOrObject){
        $model = parent::instantiateIfNecessary($arrayOrObject);
        if($model){
            $model->addAndPopulateExtraFieldsByArrayOrObject($arrayOrObject);
        }
        return $model;
    }
}
