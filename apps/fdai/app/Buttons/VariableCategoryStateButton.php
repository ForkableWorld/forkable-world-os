<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons;
use App\Types\QMStr;
class VariableCategoryStateButton extends IonicButton
{
    const BUTTONS_FOLDER = parent::BUTTONS_FOLDER.'/VariableCategoryStates';
    public function __construct(string $variableCategoryName = null){
        parent::__construct();
        if($variableCategoryName){
            $this->setVariableCategoryName($variableCategoryName);
        }
    }
    /**
     * @param string $variableCategoryName
     */
    public function setVariableCategoryName(string $variableCategoryName): void{
        $this->link = QMStr::before(":", $this->link).$variableCategoryName;
        $this->setTextAndTitle($variableCategoryName." ".$this->title);
        $this->setId(QMStr::slugify($variableCategoryName).'-'.$this->getId());
    }
}
