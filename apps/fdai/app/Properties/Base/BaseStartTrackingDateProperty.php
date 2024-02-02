<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseStartTrackingDateProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'date:nullable';
	public $dbType = 'date';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Earliest date on which the user should be reminded to track in YYYY-MM-DD format';
	public $example = '2016-11-05';
	public $fieldType = 'date';
	public $fontAwesome = FontAwesome::ACTIVITY;
	public $htmlInput = 'date';
	public $htmlType = 'date';
	public $image = ImageUrls::COMBINE_NOTIFICATIONS;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'start_tracking_date';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|date';
	public $title = 'Start Tracking Date';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|date';

}
