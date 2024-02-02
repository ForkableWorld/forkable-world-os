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
use App\Models\OAClient;
class BaseEarliestMeasurementStartAtProperty extends BaseProperty{
	use IsDateTime;
	public $dbInput = 'datetime:nullable';
	public $dbType = \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = "The data and time of the earliest measurement for this variable";
	public $fieldType = self::TYPE_DATETIME;
	public $fontAwesome = FontAwesome::MEASUREMENT;
	public $format = 'date-time';
	public $htmlInput = 'date';
	public $htmlType = 'date';
	public $image = ImageUrls::MEASUREMENT;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
    public const NAME = OAClient::FIELD_EARLIEST_MEASUREMENT_START_AT;
    public $name = self::NAME;
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|date';
	public $title = 'Earliest Measurement Start';
	public $type = self::TYPE_DATETIME;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|date';

}
