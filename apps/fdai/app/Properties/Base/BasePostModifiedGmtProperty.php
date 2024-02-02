<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsDateTime;
use App\Types\MySQLTypes;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BasePostModifiedGmtProperty extends BaseProperty{
	use IsDateTime;
	public $dbInput = self::TYPE_DATETIME;
	public $dbType = MySQLTypes::TIMESTAMP;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'GMT time and date of last modification.';
	public $example = '2018-08-31 19:36:07';
	public $fieldType = self::TYPE_DATETIME;
	public $fontAwesome = FontAwesome::ANALYSIS_SETTINGS_MODIFIED;
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
	public const NAME = 'post_modified_gmt';
	public $phpType = PhpTypes::STRING;
	public $rules = 'required|date|after_or_equal:yesterday';
	public $title = 'Post Modified Gmt';
	public $type = self::TYPE_DATETIME;
	public $validations = 'required';

}
