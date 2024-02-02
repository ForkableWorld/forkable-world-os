<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsArray;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use LogicException;
use App\DataSources\Connectors\FitbitConnector;
use OpenApi\Generator;
class BaseDataSourcesCountProperty extends BaseProperty{
	use IsArray;
	public $dbInput = 'text,65535:nullable';
	public $dbType = 'text';
	public $default = Generator::UNDEFINED;
	public $description = 'Array of connector or client measurement data source names as key and number of measurements as value';
	public $fieldType = 'text';
	public $fontAwesome = FontAwesome::NEWEST_DATA;
	public $htmlInput = 'textarea';
	public $htmlType = 'textarea';
	public $image = ImageUrls::DATA_SOURCES_FACEBOOK_LOGO_FSR9P7;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'data_sources_count';
	public $phpType = PhpTypes::STRING;
	public $title = 'Data Sources Count';
	public $type = PhpTypes::ARRAY;
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
        $val = $this->getDBValue();
        if($val && $val === "0"){
            le("Trying to save '0' to data_sources_count");
        }
    }
    public function getExample(): array{
        return [FitbitConnector::DISPLAY_NAME => 100];
    }
    public function toDBValue($value):?string {
        if(is_array($value)){
            return json_encode($value);
        }
        return $value;
    }
}
