<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
class BaseLinkUpdatedProperty extends BaseUpdatedAtProperty
{
	public $dbInput = self::TYPE_DATETIME;
	public $dbType = \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE;
	public $default = 'undefined';
	public $description = 'Time and date of link update.';
	public $example = '2021-07-03 21:09:32';
	public $fieldType = self::TYPE_DATETIME;
	public $fontAwesome = FontAwesome::UPDATED;
	public $format = 'date-time';
	public $htmlInput = 'date';
	public $htmlType = 'date';
	public $image = ImageUrls::UPDATED;
	public $importance = false;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'link_updated';
            	public $order = '99';
	public $phpType = 'date';
	public $rules = 'required|date';
	public $showOnDetail = true;
	public $title = 'Link Updated';
	public $type = PhpTypes::STRING;
	public $validations = 'required|date';

}
