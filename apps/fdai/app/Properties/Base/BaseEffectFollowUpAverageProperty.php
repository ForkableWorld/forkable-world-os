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
class BaseEffectFollowUpAverageProperty extends BaseProperty{
	use IsFloat;
	public $dbInput = 'float,10,0';
	public $dbType = 'float';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Outcome Average at Follow-Up (The average value seen for the outcome during the duration of action following the onset delay of the treatment)';
	public $example = 3.3172;
	public $fieldType = 'float';
	public $fontAwesome = FontAwesome::ACTIVITY;
	public $htmlType = 'text';
	public $image = ImageUrls::COLLABORATORS;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'effect_follow_up_average';
	public $phpType = 'float';
	public $rules = 'required|numeric';
	public $title = 'Effect Follow Up Average';
	public $type = self::TYPE_NUMBER;
	public $validations = 'required|numeric';

}
