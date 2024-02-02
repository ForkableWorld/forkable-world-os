<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons;
use App\Exceptions\CommonVariableNotFoundException;
use App\Models\Variable;
use App\Traits\HasModel\HasVariable;
use App\Types\QMStr;
use App\Variables\VariableSearchResult;
class VariableDependentStateButton extends IonicButton
{
    use HasVariable;
    protected $variable;
    public $variableName;
    const BUTTONS_FOLDER = parent::BUTTONS_FOLDER.'/VariableStates';
    public function __construct($variableReminderNotificationOrName = null, array $params = []){
        if(!$variableReminderNotificationOrName){
            return;
        }
        if(is_string($variableReminderNotificationOrName)){
            $this->setVariableName($params['variableName'] = $variableReminderNotificationOrName);
        }else{
            /** @var VariableSearchResult $variableReminderNotificationOrName */
            $params['variableId'] = $variableReminderNotificationOrName->getVariableIdAttribute();
            $params['variableName'] = $variableReminderNotificationOrName->getVariableName();
            if(method_exists($variableReminderNotificationOrName, 'getUserUnit')){
                $params['unitAbbreviatedName'] = $variableReminderNotificationOrName->getUserUnit()->abbreviatedName;
            }
            $params['variableCategoryName'] = $variableReminderNotificationOrName->getQMVariableCategory()->name;
            $this->setVariableName($params['variableName'] =
                $variableReminderNotificationOrName->getVariableName());
        }
        $this->setParameters($params);
        parent::__construct();
		if($variableReminderNotificationOrName instanceof VariableSearchResult){
			$this->variable = $variableReminderNotificationOrName;
		} else {
			$this->variable = $variableReminderNotificationOrName->getVariable();
		}
        $params = $variableReminderNotificationOrName->getUrlParams();
        if($this->link){
            $this->setUrl($this->link, $params);
        }
        $this->setParameters($params);
        $this->setId($this->id."-".$variableReminderNotificationOrName->getVariableIdAttribute());
    }
    public function getVariableIdAttribute(): ?int{
        return $this->getVariable()->getId();
    }
    public function getVariableCategoryId(): int{
        return $this->getVariable()->getVariableCategoryId();
    }
    public function getVariable(): Variable{
        if(!$this->variable){
            throw new CommonVariableNotFoundException("Please provide set variable in ".static::class);
        }
        return $this->variable;
    }
    /**
     * @param string $variableName
     */
    public function setVariableName(string $variableName): void{
        $this->variableName = $variableName;
        if($url = $this->link){
            $this->link = str_replace(":variableName", $this->getUrlEncodedVariableName(), $url);
        }
        if(empty($this->title)){
            $this->setTextAndTitle($variableName);
        }
        $this->setId(QMStr::slugify($variableName).'-'.$this->getId());
    }
    /**
     * @return string
     */
    public function getVariableName(): ?string {
        if(!$this->variableName){
            return $this->variableName = $this->getVariable()->getNameAttribute();
        }
        return $this->variableName;
    }
    /**
     * @return string
     */
    public function getUrlEncodedVariableName(): string {
        return urlencode($this->getVariableName());
    }
}
