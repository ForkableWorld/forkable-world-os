<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsJsonEncoded;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseAnalysisParametersProperty extends BaseProperty{
	use IsJsonEncoded;
	public $dbInput = 'text,65535:nullable';
	public $dbType = 'text';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Additional parameters for the study such as experiment_end_time, experiment_start_time, cause_variable_filling_value, effect_variable_filling_value';
	public $fieldType = 'text';
	public $fontAwesome = FontAwesome::ANALYSIS;
	public $htmlInput = 'textarea';
	public $htmlType = 'textarea';
	public $image = ImageUrls::ANALYSIS;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'analysis_parameters';
	public $canBeChangedToNull = true;
	public $phpType = PhpTypes::ARRAY;
	public $title = 'Analysis Parameters';
	public $type = PhpTypes::ARRAY;
    public function getExample(){
        return [];
    }
}
