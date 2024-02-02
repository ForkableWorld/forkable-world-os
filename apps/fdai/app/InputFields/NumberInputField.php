<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\InputFields;
use App\Buttons\QMButton;
use App\Slim\Model\QMUnit;
class NumberInputField extends InputField {
    public $type = self::TYPE_number;
    public $maxValue;
    public $minValue;
    public $step;
    public $unitAbbreviatedName;
    /**
     * NumberInputField constructor.
     * @param string|null $displayName
     * @param string|null $key
     */
    public function __construct(string $displayName = null, string $key = null){
        parent::__construct($displayName, $key);
    }
    /**
     * @return float
     */
    public function getMaxValue(): ?float{
        return $this->maxValue;
    }
    /**
     * @param float $maxValue
     */
    public function setMaxValue(float $maxValue){
        $this->maxValue = $maxValue;
    }
    /**
     * @return float
     */
    public function getMinValue(): ?float{
        return $this->minValue;
    }
    /**
     * @param mixed $minValue
     */
    public function setMinValue($minValue){
        $this->minValue = $minValue;
    }
    /**
     * @return float
     */
    public function getStep(): ?float{
        return $this->step;
    }
    /**
     * @param float $step
     */
    public function setStep(float $step){
        $this->step = $step;
    }
    /**
     * @return string
     */
    public function getUnitAbbreviatedName(): ?string{
        return $this->unitAbbreviatedName;
    }
    /**
     * @param QMUnit $unit
     */
    public function setUnit(QMUnit $unit){
        $this->setType(InputField::TYPE_number);
        if($unit->isCurrency()){
            $this->setLabelLeft($unit->abbreviatedName);
        }else{
            $this->setLabelRight($unit->abbreviatedName);
        }
        if($unit->maximumValue !== null){
            $this->setMaxValue($unit->maximumValue);
        }
        if($unit->minimumValue !== null){
            $this->setMinValue($unit->minimumValue);
        }
        $this->unitAbbreviatedName = $unit->abbreviatedName;
    }
    /**
     * @return QMUnit
     */
    public function getQMUnit(): ?QMUnit{
        $unitName = $this->getUnitAbbreviatedName();
        if(!$unitName){
            return null;
        }
        return QMUnit::getUnitByAbbreviatedName($unitName);
    }
	/**
	 * @param QMButton[] $buttons
	 * @return void
	 */
    public function setHintUsingButtons(array $buttons): void {
        $hint = 'You can say ';
        $unit = $this->getQMUnit();
        $unitHint = $unit->getHint();
        if($unitHint){
            $hint .= $unit->getHint();
        } else {
            foreach($buttons as $button){
                $modifiedValue = $button->getParameter('modifiedValue');
                if($modifiedValue !== null){
                    $hint .= " or ".$modifiedValue;
                }
            }
        }
        foreach($buttons as $button){
            $modifiedValue = $button->getParameter('modifiedValue');
            //if(stripos($hint, " ".$button->text) !== false){continue;}
            if($modifiedValue === null){
                $hint .= " or ".strtolower($button->text);
            }
        }
        $hint .= " or I don't remember.";
        $this->setHint($hint);
    }
}
