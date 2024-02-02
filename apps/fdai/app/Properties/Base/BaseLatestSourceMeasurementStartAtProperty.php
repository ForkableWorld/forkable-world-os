<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsDateTime;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\IsTemporal;
use App\Types\MySQLTypes;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use OpenApi\Generator;
class BaseLatestSourceMeasurementStartAtProperty extends BaseProperty{
	use IsDateTime;
	public $dbInput = 'datetime:nullable';
	public $dbType = MySQLTypes::TIMESTAMP;
	public $default = Generator::UNDEFINED;
	public $description = 'Latest measurement time for this variable from any data source. ';
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
    public const NAME = UserVariable::FIELD_LATEST_SOURCE_MEASUREMENT_START_AT;
    public $name = self::NAME;
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|date';
	public $title = 'Latest Source Measurement Start';
	public $type = self::TYPE_DATETIME;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|date';
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
        /** @var UserVariable $variable */
        $variable = $this->getParentModel();
        $this->assertEarliestBeforeLatest($variable->earliest_source_measurement_start_at, $this->getDBValue());
    }
}
