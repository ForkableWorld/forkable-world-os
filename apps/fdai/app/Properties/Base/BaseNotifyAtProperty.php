<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsDateTime;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use App\Traits\PropertyTraits\IsTemporal;
class BaseNotifyAtProperty extends BaseProperty{
	use IsDateTime;
	public $dbInput = 'datetime:nullable';
	public $dbType = \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'When the notification to track should occur. ';
	public $example = '2020-07-22 18:45:00';
	public $fieldType = self::TYPE_DATETIME;
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $format = 'date-time';
	public $htmlInput = 'date';
	public $htmlType = 'date';
	public $image = ImageUrls::QUESTION_MARK;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'notify_at';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|date';
	public $title = 'Due';
	public $type = self::TYPE_DATETIME;
	public $canBeChangedToNull = false;
	public $validations = 'date';
	public function cannotBeChangedToNull(): bool{
        return parent::cannotBeChangedToNull();
    }
}
