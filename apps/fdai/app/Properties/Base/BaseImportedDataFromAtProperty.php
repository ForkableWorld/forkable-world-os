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
use App\Traits\PropertyTraits\IsTemporal;
use OpenApi\Generator;
class BaseImportedDataFromAtProperty extends BaseProperty{
	use IsDateTime;
	public $dbInput = 'datetime:nullable';
	public $dbType = MySQLTypes::TIMESTAMP;
	public $default = Generator::UNDEFINED;
	public $description = 'Earliest data that we\'ve requested from this data source ';
	public $fieldType = self::TYPE_DATETIME;
	public $fontAwesome = FontAwesome::CONNECTOR;
	public $format = 'date-time';
	public $htmlInput = 'date';
	public $htmlType = 'date';
	public $image = ImageUrls::CONNECTION;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'imported_data_from_at';
	public $phpType = PhpTypes::STRING;
	public $title = 'Imported Data From';
	public $type = self::TYPE_DATETIME;
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        $this->logInfo("TODO: Need to fix this validation");
        return;
        $this->validateTime();
    }
}
