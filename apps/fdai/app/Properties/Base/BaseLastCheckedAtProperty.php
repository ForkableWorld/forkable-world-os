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
class BaseLastCheckedAtProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'datetime:nullable';
	public $dbType = \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'last_checked_at';
	public $example = '2020-09-02 01:04:41';
	public $fieldType = self::TYPE_DATETIME;
	public $fontAwesome = FontAwesome::CARD_LAST_FOUR;
	public $format = 'date-time';
	public $htmlInput = 'date';
	public $htmlType = 'date';
	public $image = ImageUrls::AUDIO_AND_VIDEO_CONTROLS_CHECKED_1;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'last_checked_at';
	public $canBeChangedToNull = true;
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|date';
	public $title = 'Last Checked';
	public $type = PhpTypes::STRING;
	public $validations = 'nullable|date';

}
