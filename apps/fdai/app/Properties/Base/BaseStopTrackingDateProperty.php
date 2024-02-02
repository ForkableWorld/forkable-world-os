<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Astral\Filters\ReminderNotificationsEnabledFilter;
use App\Traits\PropertyTraits\IsDate;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use App\Http\Requests\AstralRequest;
use App\Http\Requests\ResourceIndexRequest;
class BaseStopTrackingDateProperty extends BaseProperty{
    use IsDate;
	public $dbInput = 'date:nullable';
	public $dbType = 'date';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Latest date on which the user should be reminded to track  in YYYY-MM-DD format';
	public $example = '2018-03-03';
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
	public const NAME = 'stop_tracking_date';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|date';
	public $title = 'End Date';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|date';

}
