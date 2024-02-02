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
class BasePushNotificationsEnabledProperty extends BaseProperty{
	use IsBoolean;
	public $dbInput = 'boolean:nullable';
	public $dbType = self::TYPE_BOOLEAN;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Should we send the user push notifications?';
	public $example = true;
	public $fieldType = self::TYPE_BOOLEAN;
	public $fontAwesome = FontAwesome::PUSH_NOTIFICATIONS_ENABLED;
	public $htmlInput = 'checkbox,1';
	public $htmlType = 'checkbox';
	public $image = ImageUrls::PUSH_NOTIFICATIONS_ENABLED;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'push_notifications_enabled';
	public $phpType = PhpTypes::BOOL;
	public $rules = 'nullable|boolean';
	public $title = 'Push Notifications Enabled';
	public $type = self::TYPE_BOOLEAN;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|boolean';

}
