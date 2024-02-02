<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\InputFields;
class TextAreaInputField extends StringInputField {
    public $type = self::TYPE_text_area;
    public $maxRows;
    /**
     * TextAreaInputField constructor.
     * @param string|null $displayName
     * @param string|null $key
     */
    public function __construct(string $displayName = null, string $key = null){
        parent::__construct($displayName, $key);
    }
    /**
     * @return int
     */
    public function getMaxRows(){
        return $this->maxRows;
    }
    /**
     * @param mixed $maxRows
     */
    public function setMaxRows(int $maxRows){
        $this->maxRows = $maxRows;
    }
}
