<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\InputFields;
class CheckBoxInputField extends InputField {
    public $type = self::TYPE_check_box;
    /**
     * CheckBoxInputField constructor.
     * @param string|null $displayName
     * @param string|null $key
     */
    public function __construct(string $displayName = null, string $key = null){
        parent::__construct($displayName, $key);
    }
}
