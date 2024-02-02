<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsBoolean;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use App\VariableRelationships\QMVariableRelationship;
class BaseInterestingVariableCategoryPairProperty extends BaseProperty{
	use IsBoolean;
    use \App\Traits\PropertyTraits\IsCalculated;
	public $dbInput = 'boolean:nullable';
	public $dbType = self::TYPE_BOOLEAN;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'True if the combination of cause and effect variable categories are generally interesting.  For instance, treatment cause variables paired with symptom effect variables are interesting. ';
	public $fieldType = self::TYPE_BOOLEAN;
	public $fontAwesome = FontAwesome::MANAGE_VARIABLES;
	public $htmlInput = 'checkbox,1';
	public $htmlType = 'checkbox';
	public $image = ImageUrls::PRIMARY_OUTCOME_VARIABLE;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'interesting_variable_category_pair';
	public $phpType = PhpTypes::BOOL;
	public $rules = 'required|boolean';
	public $title = 'Interesting Variable Category Pair';
	public $type = self::TYPE_BOOLEAN;
	public $validations = 'required|boolean';
    /**
     * @param QMVariableRelationship $model
     * @return bool
     */
    public static function calculate($model): bool{
        $val = true;
        if(!$model->getCauseVariableIsPredictor()){
            $val = false;
        }
        if(!$model->getEffectVariableIsOutcome()){
            $val = false;
        }
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
}
