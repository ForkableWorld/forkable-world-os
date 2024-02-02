<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsFloat;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseZScoreProperty extends BaseProperty{
	use IsFloat;
	public $dbInput = 'float,10,0';
	public $dbType = 'float';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'The absolute value of the change over duration of action following the onset delay of treatment divided by the baseline outcome relative standard deviation. A.K.A The number of standard deviations from the mean. A zScore > 2 means pValue < 0.05 and is typically considered statistically significant.';
	public $example = 0.36102;
	public $fieldType = 'float';
	public $fontAwesome = FontAwesome::OAUTH_ACCESS_TOKEN;
	public $htmlType = 'text';
	public $image = ImageUrls::CLIENT_ID;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'z_score';
	public $phpType = 'float';
	public $rules = 'required|numeric';
	public $title = 'Z Score';
	public $type = self::TYPE_NUMBER;
	public $validations = 'required|numeric';

}
