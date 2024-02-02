<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\InputFields;
use App\Buttons\QMButton;
use App\Slim\Model\StaticModel;
class InputField extends StaticModel{
    public const TYPE_check_box = 'check_box';
    public const TYPE_date = 'date';
    public const TYPE_email = 'email';
    public const TYPE_number = 'number';
    public const TYPE_integer = 'integer';
    public const TYPE_postal_code = 'postal_code';
    public const TYPE_select_option = 'select_option';
    public const TYPE_string = 'string';
    public const TYPE_switch = 'switch';
    public const TYPE_text_area = 'text_area';
    public const TYPE_time = 'time';
    public const TYPE_unit = 'unit';
    public const TYPE_variable_category = 'variable_category';
    public $disabled;
    public $displayName;
    public $entityName;
    public $helpText;
    public $hint;
    public $icon;
    public $image;
    public $key;
    public $labelRight;
    public $labelLeft;
    public $link;
    public $placeholder;
    public $required;
    public $show;
    public $submitButton;
    public $type;
    public $validationPattern;
    public $value;
	/**
	 * Field constructor.
	 * @param string|null $displayName
	 * @param string|null $key
	 * @param null $value
	 * @param string|null $type
	 * @param string|null $helpText
	 * @param bool|null $show
	 * @param string|null $link
	 * @param string|null $image
	 */
    public function __construct(string $displayName = null, string $key = null, $value = null, string $type = null,
                                string $helpText = null, bool $show = null, string $link = null, string $image = null){
        if(!empty($displayName)){
            $this->displayName = $displayName;
        }
        if(!empty($key)){
            $this->key = $key;
        }
        if($value !== null){
            $this->value = $value;
        }
        if($type){
            $this->type = $type;
        }
        if(!empty($helpText)){
            $this->helpText = $helpText;
        }
        if($show !== null){
            $this->show = $show;
        }
        if(!empty($link)){
            $this->link = $link;
        }
        if(!empty($image)){
            $this->image = $image;
        }
    }
    /**
     * @return string
     */
    public function getTitleAttribute(): string{
        return $this->displayName;
    }
    /**
     * @param string $displayName
     */
    public function setDisplayName(string $displayName){
        $this->displayName = $displayName;
    }
    /**
     * @param string $helpText
     */
    public function setHelpText(string $helpText){
        $this->helpText = $helpText;
    }
    /**
     * @return string
     */
    public function getImage(): string{
        return $this->image;
    }
    /**
     * @param string $image
     */
    public function setImage(string $image){
        $this->image = $image;
    }
    /**
     * @return string
     */
    public function getKey(): string{
        return $this->key;
    }
    /**
     * @param string $key
     */
    public function setKey(string $key){
        $this->key = $key;
    }
    /**
     * @return string
     */
    public function getLink(): string{
        return $this->link;
    }
    /**
     * @param string $link
     */
    public function setLink(string $link){
        $this->link = $link;
    }
    /**
     * @param bool $required
     */
    public function setRequired(bool $required){
        $this->required = $required;
    }
    /**
     * @param bool $show
     */
    public function setShow(bool $show){
        $this->show = $show;
    }
    /**
     * @return string
     */
    public function getType(): string{
        return $this->type;
    }
    /**
     * @param string $type
     */
    public function setType(string $type){
        $this->type = $type;
    }
    /**
     * @return mixed
     */
    public function getValue(){
        return $this->value;
    }
    /**
     * @param mixed $value
     */
    public function setValue($value){
        $this->value = $value;
    }
    /**
     * @return string
     */
    public function getIcon(): string{
        return $this->icon;
    }
    /**
     * @param string $icon
     */
    public function setIcon(string $icon){
        $this->icon = $icon;
    }
    /**
     * @return string
     */
    public function getHint(): string{
        return $this->hint;
    }
    /**
     * @param string $labelLeft
     */
    public function setLabelLeft(string $labelLeft){
        $this->labelLeft = $labelLeft;
    }
    /**
     * @param string $labelRight
     */
    public function setLabelRight(string $labelRight){
        $this->labelRight = $labelRight;
    }
    /**
     * @param QMButton $submitButton
     */
    public function setSubmitButton(QMButton $submitButton){
        $this->submitButton = $submitButton;
    }
    /**
     * @param string $hint
     * @return string
     */
    public function setHint(string $hint): string {
        return $this->hint = $hint;
    }
    /**
     * @param bool $disabled
     */
    public function setDisabled(bool $disabled){
        $this->disabled = $disabled;
    }
}
