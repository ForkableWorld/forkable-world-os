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
class BasePostDateGmtProperty extends BaseProperty{
	use IsDateTime;
	public $dbInput = self::TYPE_DATETIME;
	public $dbType = \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'GMT time and date of creation. The GMT time and date is stored so there is no dependency on a site’s timezone in the future.';
	public $example = '2018-08-31 19:36:07';
	public $fieldType = self::TYPE_DATETIME;
	public $fontAwesome = FontAwesome::POST;
	public $format = 'date-time';
	public $htmlInput = 'date';
	public $htmlType = 'date';
	public $image = ImageUrls::POST;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $canBeChangedToNull = false;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'post_date_gmt';
	public $phpType = PhpTypes::STRING;
	public $rules = 'required|date|after_or_equal:2000-01-01';
	public $title = 'Post Date Gmt';
	public $type = self::TYPE_DATETIME;
	public $validations = 'required';

}
