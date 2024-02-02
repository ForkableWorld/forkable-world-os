<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Properties\BaseProperty;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class BaseEndedAtProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsDateTime;
	public $canBeChangedToNull = true;
	public $dbInput = 'datetime:nullable';
	public $dbType = self::TYPE_DATETIME;
	public $default = 'undefined';
	public $description = 'ended_at';
	public $fieldType = self::TYPE_DATETIME;
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $format = 'date-time';
	public $htmlInput = 'date';
	public $htmlType = 'date';
	public $image = ImageUrls::QUESTION_MARK;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'ended_at';
	public $order = 99;
	public $phpType = 'date';
	public $showOnDetail = true;
	public $title = 'Ended';
	public $type = 'string';

}
