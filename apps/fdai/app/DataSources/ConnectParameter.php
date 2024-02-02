<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources;
class ConnectParameter {
    /**
     * What the user sees next to this field
     * @var string
     */
    public $displayName;
    /**
     * The name or key of this parameter
     * @var string
     */
    public $key;
    /**
     * The type of this parameter (should be compatible with the HTML input tag)
     * @var string
     */
    public $type;
    /**
     * placeholder for this input tag
     * @var string
     */
    public $placeholder;
    /**
     * default value
     * @var string
     */
    public $defaultValue;
    public $helpText;
    public $ionIcon;
    /**
     * @param string $displayName
     * @param string $key
     * @param string $type
     * @param string $placeholder
     * @param string $defaultValue
     */
    public function __construct($displayName, $key, $type, $placeholder = '', $defaultValue = ''){
        $this->displayName = $displayName;
        $this->key = $key;
        $this->type = $type;
        $this->placeholder = $placeholder;
        $this->defaultValue = $defaultValue;
        $this->helpText = $defaultValue;
    }
    /**
     * @param string $helpText
     */
    public function setHelpText(string $helpText){
        $this->helpText = $helpText;
    }
    /**
     * @param mixed $ionIcon
     */
    public function setIonIcon($ionIcon){
        $this->ionIcon = $ionIcon;
    }
}
