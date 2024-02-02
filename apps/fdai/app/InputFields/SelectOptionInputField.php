<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\InputFields;
class SelectOptionInputField extends InputField {
    public $type = self::TYPE_select_option;
    public $options;
    /**
     * SelectOptionInputField constructor.
     * @param string|null $displayName
     * @param string|null $key
     */
    public function __construct(string $displayName = null, string $key = null){
        parent::__construct($displayName, $key);
    }
    /**
     * @return array
     */
    public function getOptions(){
        return $this->options;
    }
    /**
     * @param array $options
     */
    public function setOptions(array $options){
        $this->options = $options;
    }
}
