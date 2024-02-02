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
use App\Models\GlobalVariableRelationship;
class BaseNewestDataAtProperty extends BaseProperty{
	use IsDateTime;
	public $dbInput = 'datetime:nullable';
	public $dbType = \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = "The date and time of the most recent data point for this analysis";
	public $example = '2020-06-05 15:57:00';
	public $fieldType = self::TYPE_DATETIME;
	public $fontAwesome = FontAwesome::ANALYSIS;
	public $format = 'date-time';
	public $htmlInput = 'date';
	public $htmlType = 'date';
	public $image = ImageUrls::AGGREGATE_CORRELATION;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
    public const NAME = GlobalVariableRelationship::FIELD_NEWEST_DATA_AT;
    public $name = self::NAME;
	public $canBeChangedToNull = true;
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|date';
	public $title = 'Newest Data';
	public $type = self::TYPE_DATETIME;
	public $validations = 'nullable|date';
}
