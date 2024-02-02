<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsDateTime;
use App\Models\ConnectorImport;
use App\Traits\PropertyTraits\IsTemporal;
use App\Types\MySQLTypes;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use App\Models\Connection;
use OpenApi\Generator;
class BaseLatestMeasurementAtProperty extends BaseProperty{
	use IsDateTime;
	public $dbInput = 'datetime:nullable';
	public $dbType = MySQLTypes::TIMESTAMP;
	public $default = Generator::UNDEFINED;
	public $description = "The most recent measurement date and time for this connection or variable";
	public $example = '2018-03-06 00:00:00';
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
    public const NAME = Connection::FIELD_LATEST_MEASUREMENT_AT;
    public $name = self::NAME;
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|date';
	public $title = 'Latest Measurement';
	public $type = self::TYPE_DATETIME;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|date';
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
        /** @var ConnectorImport $variable */
        $variable = $this->getParentModel();
        $this->assertEarliestBeforeLatest($variable->earliest_measurement_at, $this->getDBValue());
    }
}
