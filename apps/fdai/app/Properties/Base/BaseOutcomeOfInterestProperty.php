<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsBoolean;
use App\Properties\UserVariable\UserVariableNumberOfMeasurementsProperty;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use App\Variables\QMUserVariable;
class BaseOutcomeOfInterestProperty extends BaseProperty{
	use IsBoolean;
	use \App\Traits\PropertyTraits\IsCalculated;
	public $dbInput = 'boolean:nullable';
	public $dbType = self::TYPE_BOOLEAN;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'outcome_of_interest';
	public $example = false;
	public $fieldType = self::TYPE_BOOLEAN;
	public $fontAwesome = FontAwesome::PRIMARY_OUTCOME_VARIABLE_ID;
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
	public const NAME = 'outcome_of_interest';
	public $phpType = PhpTypes::BOOL;
	public $rules = 'nullable|boolean';
	public $title = 'Outcome of Interest';
	public $type = self::TYPE_BOOLEAN;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|boolean';
    /**
     * @param QMUserVariable $model
     * @return bool
     */
    public static function calculate($model){
        if(!$model->isOutcome()){
            $model->outcomeOfInterest = 0;
            $model->setAttribute(static::NAME, 0);
            return 0;
        }
        if(!$model->outcomeOfInterest && $model->calculateNumberOfTrackingReminders()){
            $model->outcomeOfInterest = 1;
        }
        $num = UserVariableNumberOfMeasurementsProperty::calculate($model);
        if(!$model->outcomeOfInterest && $num){
            $model->outcomeOfInterest = 1;
        }
        $model->setAttribute(static::NAME, $model->outcomeOfInterest);
        return $model->outcomeOfInterest;
    }

}
