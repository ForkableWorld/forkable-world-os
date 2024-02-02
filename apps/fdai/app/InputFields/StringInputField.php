<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\InputFields;
class StringInputField extends InputField {
    public $type = self::TYPE_string;
    public $maxLength;
    public $minLength;
    /**
     * StringInputField constructor.
     * @param string|null $displayName
     * @param string|null $key
     */
    public function __construct(string $displayName = null, string $key = null){
        parent::__construct($displayName, $key);
    }
    /**
     * @return int
     */
    public function getMaxLength(): int{
        return $this->maxLength;
    }
    /**
     * @param int $maxLength
     */
    public function setMaxLength(int $maxLength){
        $this->maxLength = $maxLength;
    }
    /**
     * @return int
     */
    public function getMinLength(): int{
        return $this->minLength;
    }
    /**
     * @param int $minLength
     */
    public function setMinLength($minLength){
        $this->minLength = $minLength;
    }
}
